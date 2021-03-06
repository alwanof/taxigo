<?php

namespace App\Http\Controllers;

use App\Driver;
use App\Order;
use App\Parse\Stream;
use App\Service;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;
use Illuminate\Support\Facades\Log;

class TestoController extends Controller
{

    public function test()
    {
        //$order = Order::find(118);
        //return $order->service->qactive;

        $orderKey = Order::where('session', 'TEST');
        $hasOrder = ($orderKey->count() > 0) ? true : false;
        $order = ($hasOrder) ? $orderKey->first() : null;

        $d1 = Driver::where('hash', 'h5nTcgq84J')->first();
        $d2 = Driver::where('hash', 'XEGtCWfzZI')->first();
        $d3 = Driver::where('hash', 'bWxg7tCxiG')->first();

        $driver1 = null;
        $driver2 = null;
        $driver3 = null;
        $distance = [0, 0, 0];
        $service = null;
        $office = User::withoutGlobalScope('ref')->find(21);

        if ($order) {
            $service = Service::withoutGlobalScope('ref')->find($order->service_id);
            $distance = [
                cooDistance($d1->lat, $d1->lng, $order->from_lat, $order->from_lng) * 1000,
                cooDistance($d2->lat, $d2->lng, $order->from_lat, $order->from_lng) * 1000,
                cooDistance($d3->lat, $d3->lng, $order->from_lat, $order->from_lng) * 1000,
            ];

            if ($order->office->settings['auto_fwd_order'] && $order->driver_id == null) {
                $driver1 = Http::get(env('APP_URL') . '/api/app/get/feeds/h5nTcgq84J')->json();
                $driver2 = Http::get(env('APP_URL') . '/api/app/get/feeds/XEGtCWfzZI')->json();
                $driver3 = Http::get(env('APP_URL') . '/api/app/get/feeds/bWxg7tCxiG')->json();
            } else {

                if ($order->driver_id == 6) {

                    $driver1 = Http::get(env('APP_URL') . '/api/app/get/order/h5nTcgq84J')->json();
                }
                if ($order->driver_id == 16) {

                    $driver2 = Http::get(env('APP_URL') . '/api/app/get/order/XEGtCWfzZI')->json();
                }
                if ($order->driver_id == 17) {

                    $driver3 = Http::get(env('APP_URL') . '/api/app/get/order/bWxg7tCxiG')->json();
                }
            }
        }
        $xdata = [
            'order' => $order,
            'service' => $service,
            'office' => $office,
            'driver1' => $driver1,
            'driver2' => $driver2,
            'driver3' => $driver3,
            'distance' => $distance
        ];

        //return $xdata;


        return view('test', compact(['xdata']));
    }

    public function create(Request $request)
    {
        $order = Order::create(
            [
                'session' => 'TEST',
                'name' => 'Kamal Test',
                'email' => 'test@gmail.com',
                'phone' => '905318927413',
                'from_address' => 'Mevlanakapı mah. Selamağası sokak bina 20 daire 5 Fatih İstanbul',
                'from_lat' => '41.0191247',
                'from_lng' => '28.9405414',
                'to_address' => ' Akşemsettin mah. Halıcılar sokak Fatih İstanbul',
                'to_lat' => '41.008032',
                'to_lng' => '28.9348213',
                'service_id' => $request->service_id,
                'status' => 0,
                'note' => 'some note test'
            ]
        );

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

        $action = 'create';
        $driverHashs = [];
        $forwards = $this->forwardOrder($order);
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

        return redirect(route('test.index'));
    }

    private function forwardOrder(Order $order)
    {
        if ($order->office->settings['auto_fwd_order']) {

            //workrange method
            if ($order->service->qactive && count($order->service->queues) > 0) {
                $drivers = $order->service->queues;
            } else {
                $workRange = $order->office->settings['work_rang'];
                $drivers = DB::select('SELECT *, ( 3959 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance FROM drivers where user_id=? AND busy=? HAVING distance < ?', [$order->from_lat, $order->from_lng, $order->from_lat, $order->user_id, 2, $workRange]);
            }

            $driverIDs = array_map(function ($value) {
                return $value->id;
            }, $drivers);
            $driverHashs = array_map(function ($value) {
                return $value->hash;
            }, $drivers);
            if (count($driverIDs) > 0) {
                $order->subscribers()->sync($driverIDs);
                $order->status = 13;
            } else {
                $order->status = 99;
            }

            $order->save();

            return $driverHashs;
        }
        return false;
    }

    public function reset()
    {
        $r1 = rand(0, 9);
        $r2 = rand(0, 9);
        $r3 = rand(0, 9);
        $r4 = rand(0, 9);
        $r5 = rand(0, 9);
        $r6 = rand(0, 9);
        $order = Order::where('session', 'TEST')->first();
        $order->subscribers()->sync([]);
        $order->delete();
        Driver::where('id', 6)->update([
            'busy' => 2,
            'lng' => '28.94' . $r1 . '355',
            'lat' => '41.022' . $r2 . '874'
        ]);
        Driver::where('id', 16)->update([
            'busy' => 2,
            'lng' => '28.94' . $r3 . '355',
            'lat' => '41.032' . $r4 . '874'
        ]);
        Driver::where('id', 17)->update([
            'busy' => 2,
            'lng' => '28.94' . $r5 . '355',
            'lat' => '41.052' . $r6 . '874'
        ]);

        return redirect(route('test.index'));
    }
    public function officeAccept()
    {
        $order = Order::where('session', 'TEST')->first();
        Http::get(env('APP_URL') . '/api/order/office/approve/' . $order->id)->json();

        return redirect(route('test.index'));
    }


    public function officeReject()
    {
        $order = Order::where('session', 'TEST')->first();
        Http::get(env('APP_URL') . '/api/order/office/reject/' . $order->id)->json();
        return redirect(route('test.index'));
    }


    public function driverTakeOrder(Request $request)
    {
        $order = Order::where('session', 'TEST')->first();
        Http::get(env('APP_URL') . '/api/order/office/select/' . $request->driver_id . '/to/' . $order->id)->json();
        return redirect(route('test.index'));
    }

    public function tracking(Request $request)
    {
        $order = Order::where('session', 'TEST')->first();
        $driver = Driver::find($order->driver_id);
        $loc = explode(',', $request->loc);
        Http::get(env('APP_URL') . '/api/app/' . $driver->hash . '/tracking/' . $loc[0] . '/' . $loc[1])->json();
        return redirect(route('test.index'));
    }
    public function sendOffer(Request $request)
    {
        $order = Order::where('session', 'TEST')->first();
        Http::get(env('APP_URL') . '/api/order/office/send/' . $request->total . '/to/' . $order->id)->json();
        return redirect(route('test.index'));
    }

    public function frontAccept()
    {
        $order = Order::where('session', 'TEST')->first();
        Http::get(env('APP_URL') . '/api/order/customer/approve/' . $order->id)->json();

        return redirect(route('test.index'));
    }


    public function frontReject()
    {
        $order = Order::where('session', 'TEST')->first();
        Http::get(env('APP_URL') . '/api/order/customer/reject/' . $order->id)->json();
        return redirect(route('test.index'));
    }

    public function orderStart()
    {
        $order = Order::where('session', 'TEST')->first();
        $driver = Driver::find($order->driver_id);
        Http::get(env('APP_URL') . '/api/app/' . $driver->hash . '/start/' . $order->id)->json();
        return redirect(route('test.index'));
    }

    public function orderEnd()
    {
        $order = Order::where('session', 'TEST')->first();
        $driver = Driver::find($order->driver_id);
        Http::get(env('APP_URL') . '/api/app/' . $driver->hash . '/done/' . $order->id)->json();
        return redirect(route('test.index'));
    }

    public function orderComplete(Request $request)
    {

        $order = Order::where('session', 'TEST')->first();
        $driver = Driver::find($order->driver_id);
        Http::get(env('APP_URL') . '/api/app/' . $driver->hash . '/final/' . $order->id . '/set/' . $request->total)->json();
        return redirect(route('test.index'));
    }

    public function orderAbort()
    {

        $order = Order::where('session', 'TEST')->first();

        Http::get(env('APP_URL') . '/api/app/abort/order/' . $order->id)->json();
        return redirect(route('test.index'));
    }

    public function driverAccept($driver_id)
    {
        $hash = Driver::find($driver_id)->hash;
        $order = Order::where('session', 'TEST')->first();
        Http::get(env('APP_URL') . '/api/app/' . $hash . '/approve/' . $order->id)->json();

        return redirect(route('test.index'));
    }


    public function driverReject()
    {
        $order = Order::where('session', 'TEST')->first();
        $driver = Driver::find($order->driver_id);
        Http::get(env('APP_URL') . '/api/app/' . $driver->hash . '/reject/' . $order->id)->json();
        return redirect(route('test.index'));
    }

    // move lab for driver


    public function move(Request $request, $s)
    {

        $rider = Driver::where('user_id', 21)->inRandomOrder()->first();
        if ($s == 999) {
            $request->session()->forget('point');
            $msg = ['Reset Done'];
            return view('move', compact(['s', 'msg']));
        }

        $order = Order::where('session', 'TEST')->first();
        if (!$order) {
            $this->randomMove($request, $rider->hash, [$rider->lat, $rider->lng]);
            $msg = ['No Order'];
            return view('move', compact(['s', 'msg']));
        }
        $driver = Driver::find($order->driver_id);
        if (!$driver) {
            $this->randomMove($request, $rider->hash, [$rider->lat, $rider->lng]);
            $msg = ['No Driver'];
            return view('move', compact(['s', 'msg']));
        }


        $step = 0.001;
        $point = [$driver->lat, $driver->lng];

        if ($order->status == 21) {
            $dist = [$order->from_lat, $order->from_lng];
        } else if ($order->status == 22) {
            $dist = [$order->to_lat, $order->to_lng];
        } else {
            $this->randomMove($request, $driver->hash, $point);
            $msg = 'Random Move';
            return view('move', compact(['s', 'msg']));
        }




        $moveToPath = $this->pathMove($request, $driver->hash, $dist, $point, $step);

        if (!$moveToPath) {
            Log::info('Mission Done');
            $msg = ['Mission Done'];
            return view('move', compact(['s', 'msg']));
        }

        $msg = $point;
        return view('move', compact(['s', 'msg']));
    }

    private function pathMove(Request $request, $hash, $p1, $p2, $step)
    {

        if (!$request->session()->has('point')) {
            $request->session()->put('point', $p2);
            Log::info('Start');
        }

        $p2 = session('point');
        if (!$this->star($p1, $p2, $step)) {
            return false;
        }
        $p2 = $this->star($p1, $p2, $step);
        Http::get(env('APP_URL') . '/api/app/' . $hash . '/tracking/' . $p2[0] . '/' . $p2[1])->json();

        $request->session()->put('point', $p2);
        Log::info($p2);
        return true;
    }

    private function randomMove(Request $request, $hash, $p2)
    {
        $step = rand(-50, 50) / 10000;

        if (!$request->session()->has('point')) {
            $request->session()->put('point', $p2);
            Log::info('Start Random');
        }

        $p2 = session('point');
        $p2 = $this->randomStar($p2, $step);
        Http::get(env('APP_URL') . '/api/app/' . $hash . '/tracking/' . $p2[0] . '/' . $p2[1])->json();
        $request->session()->put('point', $p2);
        Log::info($p2);
        return true;
    }

    private function star($x, $y, $w)
    {
        Log::info('Distance:' . abs($x[0] - $y[0]) . ',' . abs($x[1] - $y[1]));
        if (abs($x[0] - $y[0]) <= $w || abs($x[1] - $y[1]) <= $w) {
            return false;
        }
        return [
            ($x[0] > $y[0]) ? $y[0] + $w : $y[0] - $w,
            ($x[1] > $y[1]) ? $y[1] + $w : $y[1] - $w,
        ];
    }

    private function randomStar($y, $w)
    {
        Log::info('Random:');
        Log::info($y);
        return [
            $y[0] + $w,
            $y[1] + $w,
        ];
    }
}
