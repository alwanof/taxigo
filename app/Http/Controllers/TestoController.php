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
use Illuminate\Support\Facades\Http;

class TestoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function test()
    {


        $orderKey = Order::where('session', 'TEST');
        $hasOrder = ($orderKey->count() > 0) ? true : false;
        $order = ($hasOrder) ? $orderKey->first() : null;

        $driver1 = null;
        $driver2 = null;
        $service = null;
        //$driver1 = Http::get(env('APP_URL') . '/api/app/get/order/h5nTcgq84J')->json();
        //return $driver1;

        if ($order) {
            $service = Service::withoutGlobalScope('ref')->find($order->service_id);

            if ($order->driver_id == 6) {

                $driver1 = Http::get(env('APP_URL') . '/api/app/get/order/h5nTcgq84J')->json();
            }
            if ($order->driver_id == 16) {

                $driver2 = Http::get(env('APP_URL') . '/api/app/get/order/XEGtCWfzZI')->json();
            }
        }
        $xdata = [
            'order' => $order,
            'service' => $service,
            'driver1' => $driver1,
            'driver2' => $driver2,
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

        $this->forwardOrder($order);

        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'C',
            'meta' => ['office' => $order->office->id, 'action' => 'create']
        ]);

        return redirect(route('test.index'));
    }

    private function forwardOrder(Order $order)
    {
        if ($order->office->settings['auto_fwd_order']) {
            $drivers = Driver::where([
                'user_id' => $order->user_id,
                'busy' => 2
            ])->pluck('id');
            $order->subscribers()->sync([$drivers]);
            $order->status = 13;
            $order->save();
            return true;
        }
        return false;
    }
    public function reset()
    {
        $r1 = rand(0, 9);
        $r2 = rand(0, 9);
        $r3 = rand(0, 9);
        $r4 = rand(0, 9);

        Order::where('session', 'TEST')->delete();
        Driver::where('id', 6)->update([
            'busy' => 2,
            'lng' => '28.94' . $r1 . '355',
            'lat' => '41.022' . $r2 . '874'
        ]);
        Driver::where('id', 16)->update([
            'busy' => 2,
            'lng' => '28.94' . $r3 . '355',
            'lat' => '41.022' . $r4 . '874'
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

    public function driverAccept()
    {
        $order = Order::where('session', 'TEST')->first();
        Http::get(env('APP_URL') . '/api/app/approve/' . $order->id)->json();

        return redirect(route('test.index'));
    }


    public function driverReject()
    {
        $order = Order::where('session', 'TEST')->first();
        $driver = Driver::find($order->driver_id);
        Http::get(env('APP_URL') . '/api/app/' . $driver->hash . '/reject/' . $order->id)->json();
        return redirect(route('test.index'));
    }
}
