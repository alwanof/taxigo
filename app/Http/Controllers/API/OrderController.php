<?php

namespace App\Http\Controllers\API;

use App\Driver;
use App\Http\Controllers\Controller;
use App\Order;
use App\Parse\Stream;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function getUserOrders($id)
    {
        $user = User::findOrFail($id);
        if ($user->level == 2) {

            return
                Order::where('user_id', $user->id)
                ->whereIn('status', [0, 1, 12, 2, 21, 3])
                ->orderBy('updated_at', 'DESC')
                ->get();
        }
        return [];
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
        $order = Order::find($order);

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
        $order->offer = round(floatVal($offer), 2);
        $order->save();

        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'U',
            'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
        ]);


        return $order;
    }

    public function getDriverOrder($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $pendingOrder = Order::where([
            'driver_id' => $driver->id,
            'status' => 21
        ]);
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

        return false;
    }
    public function driverApproveOrder($order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->status = 21;
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
        $order->status = 1;
        $order->driver_id = null;
        $order->block = ($order->block == null) ? $driver->id : '--' . $driver->id;
        $order->save();

        $driver->busy = 2;
        $driver->save();

        $office = User::findOrFail($order->user_id);

        if ($office->settings['auto_fwd_order']) {
            $block = explode('--', $order->block);
            $driver = Driver::where('user_id', $order->user_id)
                ->where('busy', 2)
                ->whereNotIn('id', $block)
                ->inRandomOrder()
                ->first();
            if ($driver) {
                $order->driver_id = $driver->id;
                $order->status = 2;
                $order->save();
            } else {
                $order->status = 91;
                $order->save();
            }
        }
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
        $order->status = 9;
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
