<?php

namespace App\Http\Controllers\API;

use App\Driver;
use App\Http\Controllers\Controller;
use App\Order;
use App\Parse\Stream;
use App\Queue;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function getUserOrders($id)
    {
        $user = User::findOrFail($id);
        if ($user->level == 2) {

            return
                Order::with('service')->where('user_id', $user->id)
                ->whereIn('status', [0, 1, 12, 2, 21, 22, 3])
                ->orderBy('updated_at', 'DESC')
                ->get();
        }
        return [];
    }

    public function initOrder($officeEmail)
    {

        $office = User::where('email', $officeEmail)->firstOrFail();

        if (count($office->services) == 0) {
            return response('NO_SERVICE', 400);
        }


        if ($office->level != 2) return response('BAD_REQUEST', 400);;
        $agent = $office->parent;


        $mapCenter = [$office->settings['coordinate_lat'], $office->settings['coordinate_lng']];
        return [
            'office' => $office,
            'agent' => $agent,
            'mapCenter' => $mapCenter
        ];
    }

    public function newOrder(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'from_address' => 'required',
            'from_lat' => 'required',
            'from_lng' => 'required',
            'service_id' => 'required',
            'office_id' => 'required'
        ]);


        $office = User::findOrFail($request->office_id);
        $agent = User::findOrFail($office->ref);

        // Create a new Order

        $order = Order::create(
            [
                'session' => Str::random(28),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'from_address' => $request->from_address,
                'from_lat' => $request->from_lat,
                'from_lng' => $request->from_lng,
                'to_address' => ($request->to_address) ? $request->to_address : null,
                'to_lat' => ($request->to_lat || ($request->to_lat != $request->from_lat)) ? $request->to_lat : 0,
                'to_lng' => ($request->to_lng || ($request->to_lng != $request->from_lng)) ? $request->to_lng : 0,
                'service_id' => $request->service_id,
                'user_id' => $office->id,
                'parent' => $agent->id,
                'status' => 0,
                'note' => $request->note
            ]
        );

        $this->est_stuff($order);
        $action = 'create';
        $driverHashs = [];
        //forward & filter
        //filters=[ luggage(N) , pet_friendly(NO) , child_seat(0) , wifi(0) , creditcard (0) ]
        $filters = [
            (isset($request->luggage)) ? $request->luggage : 'N',
            (isset($request->pet_friendly)) ? 1 : 0,
            (isset($request->child_seat)) ? 1 : 0,
            (isset($request->wifi)) ? 1 : 0,
            (isset($request->creditcard)) ? 1 : 0
        ];
        $forwards = $this->forwardOrder($order, $filters);

        // \forward & filter
        if ($forwards) {
            $action = 'forward';
            $driverHashs = $forwards;
        }
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'C',
            'meta' => ['office' => $order->office->id, 'drivers' => $driverHashs, 'action' => $action]
        ]);
        return $order;
    }
    private function est_stuff($order)
    {
        if ($order->from_lat != 0 && $order->from_lng != 0   && $order->to_lat != 0  && $order->to_lng != 0) {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'key' => 'AIzaSyBBygkRzIk31oyrn9qtVvQmxfdy-Fhjwz0',
                'language' => 'en-US',
                'mode' => 'DRIVING',
                'origins' => $order->from_lat . ',' . $order->from_lng,
                'destinations' => $order->to_lat . ',' . $order->to_lng,
            ]);

            if ($response['status'] == 'OK' && $response['rows'][0]['elements'][0]['status'] == 'OK') {
                $order->est_distance = $response['rows'][0]['elements'][0]['distance']['value'];
                $order->est_time = $response['rows'][0]['elements'][0]['duration']['value'];
                $order->est_price = $order->orderTotal($order->est_distance, $order->est_time);
                $order->save();
            }
        }
        return true;
    }
    private function forwardOrder($order, $filters = ['N', 'NO', 0, 0, 0])
    {
        //filters=[ luggage(N) , pet_friendly(NO) , child_seat(0) , wifi(0) , creditcard (0) ]
        if ($order->office->settings['auto_fwd_order']) {
            $whereFilters = '';
            if ($filters[0] != 'N') {
                $whereFilters = $whereFilters . " AND luggage='" . $filters[0] . "'";
            }
            if ($filters[1] != 'NO') {
                $whereFilters = $whereFilters . " AND pet_friendly='" . $filters[1] . "'";
            }
            if ($filters[2] != 0) {
                $whereFilters = $whereFilters . " AND child_seat='" . $filters[2] . "'";
            }
            if ($filters[3] != 0) {
                $whereFilters = $whereFilters . " AND wifi='" . $filters[3] . "'";
            }
            if ($filters[4] != 0) {
                $whereFilters = $whereFilters . " AND creditcard='" . $filters[4] . "'";
            }
            //workrange method
            if ($order->service->queues) {
                if ($order->service->qactive && count($order->service->queues) > 0) {
                    $drivers = $order->service->queues;
                }
            } else {
                $workRange = $order->office->settings['work_rang'];
                $drivers = DB::select('SELECT *, ( 3959 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance FROM drivers where user_id=? AND busy=? ' . $whereFilters . ' HAVING distance < ?', [$order->from_lat, $order->from_lng, $order->from_lat, $order->user_id, 2, $workRange]);
            }

            $driverIDs = array_map(function ($value) {
                return $value->id;
            }, $drivers);
            $driverHashs = array_map(function ($value) {
                return $value->hash;
            }, $drivers);

            if ($driverIDs) {
                if (count($driverIDs) > 0) {
                    $order->subscribers()->sync($driverIDs);
                    $order->status = 13;
                } else {
                    $driver = Driver::where([
                        'busy' => 2,
                        'user_id' => $order->user_id

                    ])->inRandomOrder()->first();
                    if ($driver) {
                        $order->subscribers()->sync($driver);
                        $order->status = 13;
                    } else {
                        $order->status = 93;
                    }
                }
            }


            $order->save();

            return $driverHashs;
        }
        return false;
    }

    public function create(Request $request)
    {
        $user = User::find($request->uid);

        $order = Order::create(
            [
                'session' => $request->session,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'from_address' => $request->from_address,
                'from_lat' => $request->from_lat,
                'from_lng' => $request->from_lng,
                'user_id' => $user->id,
                'parent' => $user->parent->id
            ]
        );
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'C',
            'meta' => ['office' => $user->id, 'agent' => $user->parent->id, 'action' => 'create']
        ]);

        return $order;
    }

    public function update(Request $request)
    {
        $order = Order::find(1);
        $order->title = 'title 1 99';
        $order->save();
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);


        return $order;
    }

    public function trash(Request $request)
    {
        $order = Order::find(17);
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'D',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'delete']
        ]);

        $order->delete();

        return $order;
    }

    public function getOrder($order)
    {
        $order = Order::with('service')->find($order);

        return $order;
    }

    public function cancel($order)
    {
        $order = Order::find($order);
        $order->status = 99;
        $order->save();
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'cancel']
        ]);
        return $order;
    }

    public function reject($order)
    {
        $order = Order::find($order);
        $order->status = 91;
        $order->save();
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);

        return $order;
    }

    public function undo($order)
    {
        $order = Order::find($order);
        $driver = Driver::find($order->driver_id);
        $driver->busy = 2;
        $driver->save();

        $order->status = 1;
        $order->driver_id = null;
        $order->save();

        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $order->user_id, 'agent' => $order->parent, 'action' => 'undo']
        ]);

        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent, 'action' => 'undo']
        ]);

        return $order;
    }

    public function customerReject($order)
    {
        $order = Order::find($order);
        $order->status = 92;
        $order->save();
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);


        return $order;
    }

    public function approve($order)
    {
        $order = Order::find($order);
        $order->status = 1;
        $order->save();
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);
        return $order;
    }

    public function customerApprove($order)
    {
        $order = Order::find($order);
        $order->status = 1;
        $order->save();
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);
        return $order;
    }
    public function selectDriver($driver, $order)
    {
        $driver = Driver::find($driver);
        $order = Order::find($order);
        $order->status = 2;
        $order->driver_id = $driver->id;
        $order->save();

        $driver->busy = 1;
        $driver->save();
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);
        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
        ]);


        $this->sendMobileNoti('New Order!', 'You have been got a new order', $driver->hash);
        return $order;
    }

    public function sendOffer($offer, $order)
    {
        $order = Order::find($order);
        $order->status = 3;
        $order->total = round(floatVal($offer), 2);
        $order->save();

        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);


        return $order;
    }

    public function getDriversOffice($officeEmail)
    {
        $office = User::where('email', $officeEmail)->first();
        $drivers = Driver::where('user_id', $office->id)->get();
        return response($drivers, 200);
    }

    public function getDriverOrder($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $pendingOrder = Order::where('driver_id', $driver->id,)
            ->whereIn('status', [21, 22, 23]);
        if ($pendingOrder->count() > 0) {
            return $pendingOrder->first();
        }

        $newOrder = Order::where([
            'driver_id' => $driver->id,
            'status' => 2
        ]);
        if ($newOrder->count() > 0) {
            return $newOrder->first();
        }

        return response(0, 200);
    }
    public function getDriverFeed($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        return $driver->requests()->where('status', 13)->first();
    }

    public function driverApproveOrder($hash, $order_id)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $order = Order::findOrFail($order_id);
        $order->status = 21;
        if ($order->driver_id != $driver->id) {
            $order->driver_id = $driver->id;
            $driver->busy = 1;
            $driver->save();
            Stream::create([
                'pid' => $order->id,
                'model' => 'Order',
                'action' => 'U',
                'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent, 'action' => 'unsubscribe']
            ]);
        }

        $order->save();

        $order->subscribers()->sync([]);
        //remove from Queue
        Queue::where('driver_id', $driver->id)->delete();

        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);

        return response(1, 200);
    }

    public function driverStartOrder($hash, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->status = 22;
        $order->save();
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);

        return response(1, 200);
    }

    public function driverRejectOrder($hash, $order_id)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();

        $order = Order::findOrFail($order_id);
        $office = User::findOrFail($order->user_id);
        if ($office->settings['auto_fwd_order']) {
            $driver->requests()->sync([]);
            Stream::create([
                'pid' => $order->id,
                'model' => 'Order',
                'action' => 'U',
                'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
            ]);
            Stream::create([
                'pid' => $driver->id,
                'model' => 'Driver',
                'action' => 'U',
                'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
            ]);

            return response(1, 200);
        }
        $order->status = 1;
        $order->driver_id = null;
        $order->block = ($order->block == null) ? $driver->id : '--' . $driver->id;
        $order->save();

        $driver->busy = 2;
        $driver->save();




        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);
        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
        ]);

        return response(1, 200);
    }

    public function driverAbortOrder($hash, $order_id)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();

        $order = Order::findOrFail($order_id);
        $order->status = 90;
        $order->save();

        $driver->busy = 2;
        $driver->save();

        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);
        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
        ]);

        return response(1, 200);
    }

    public function driverCompleteOrder($hash, $order_id)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $order = Order::findOrFail($order_id);
        $order->total = $order->orderTotal($order->distance, $order->duration);

        $order->status = ($order->service->plan == 'DRIVER') ? 23 : 9;
        $order->save();


        $driver->busy = ($order->service->plan == 'DRIVER') ? 1 : 2;
        $driver->save();

        $responseCode = 1; // 1 NONE , 2 OFFER , 3 DRIVER , 4 TRACK;
        switch ($order->service->plan) {
            case 'OFFER':
                $responseCode = 2;
                break;
            case 'DRIVER':
                $responseCode = 3;

                break;
            case 'TRACK':
                $responseCode = 4;
                break;
        }

        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);
        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
        ]);

        return response($responseCode, 200);
    }

    public function finalCompleteOrder($hash, $order_id, $total)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $order = Order::findOrFail($order_id);

        $order->status = 9;
        $order->total = $total;
        $order->save();

        $driver->busy = 2;
        $driver->save();



        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);
        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
        ]);

        return response(1, 200);
    }

    private function sendMobileNoti($title, $body, $token)
    {

        $response = Http::withHeaders([
            'X-Parse-Application-Id' => 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
            'X-Parse-REST-API-Key' => 'ozmiEzNHJIAb3EqCD9lislhOC5dPsC0OS18DFJ6j',
            'Content-Type' => 'application/json'
        ])->post('https://parseapi.back4app.com/functions/gettoken', [
            'hash' => $token,
        ]);

        try {
            $SERVER_API_KEY = 'AAAAwpX5cTo:APA91bG5qS4xNQCAdOxn8N2tVhkFR7nHsk8smxNTgw-Lh-ceWtxuYXwdhsGadenH3wrrKsA96pg5KDu7cA9JssEyp_LjKA99xEYpernypzDbVFqqzLTO8BLpyALDLcnwAhNKCXmHCD4s';
            $data = [
                "registration_ids" => [
                    $response['result']['token']
                ],
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                    "sound" => "default"
                ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);
        } catch (\Throwable $th) {
            return 0;
        }
        return 1;
    }
}
