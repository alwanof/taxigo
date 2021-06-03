<?php

namespace App\Http\Controllers\API;

use App\Driver;
use App\Http\Controllers\Controller;
use App\Order;
use App\Parse\Stream;
use App\User;
use App\Queue;
use App\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    public function getUserDrivers($user)
    {
        $user = User::findOrFail($user);
        switch ($user->level) {
            case 2:
                return Driver::where('user_id', $user->id)
                    ->where('busy', '>', 0)
                    ->orderBy('distance', 'ASC')
                    ->get();
                break;
            case 1:
                return Driver::where('parent', $user->id)
                    ->where('busy', '>', 0)
                    ->orderBy('distance', 'ASC')
                    ->get();
                break;
        }
        return Driver::all();
    }

    public function getDriver($driver)
    {

        $driver = Driver::find($driver);
        if (is_object($driver)) {
            return $driver;
        }
        return false;
    }

    public function tracking($hash, $lat, $lng)
    {

        $driver = Driver::where('hash', $hash)->firstOrFail();
        $office = User::find($driver->user_id);
        $order = Order::where('driver_id', $driver->id)
            ->where('status', 22)
            ->first();
        if ($order) {
            $coordinates = [
                'oldLat' => $driver->lat,
                'oldLng' => $driver->lng,
                'newLat' => $lat,
                'newLng' => $lng
            ];
            $order->distance = $this->orderMetric($coordinates, $order->distance);
            $order->duration = $order->duration + diffSeconds($driver->updated_at);
            $order->total = $order->orderTotal($order->distance, $order->duration);

            //if ($order->service->plan != 'OFFER') {}

            $order->save();
            Stream::create([
                'pid' => $order->id,
                'model' => 'Order',
                'action' => 'U',
                'meta' => ['office' => $order->user_id, 'agent' => $order->parent, 'action' => 'update']
            ]);
        }


        //Update driver coordinates
        $olat = $office->settings['coordinate_lat'];
        $olng = $office->settings['coordinate_lng'];
        $distance = cooDistance($olat, $olng, $lat, $lng) * 1000;

        $driver->lat = $lat;
        $driver->lng = $lng;
        $driver->distance = $distance;
        $driver->save();

        //check if there is queue:
        //&& count($order->service->queues) > 0

        $qStatus = 0;
        $qservice = Service::where(['vehicle_id' => $driver->vehicle_id, 'user_id' => $office->id])->first();

        if ($qservice->qactive) {
            $queue = Queue::where(['driver_id' => $driver->id, 'service_id' => $qservice->id])->first();

            if ($queue) {

                if ($driver->distance > $office->settings['queue_range']) {

                    Http::get(env('APP_URL') . '/api/app/' . $driver->hash . '/queue/detach')->json();
                    $qStatus = 0;
                } else {
                    $qStatus = 2;
                }
            } else {
                if ($driver->distance < $office->settings['queue_range']) {
                    $qStatus = 1;
                }
            }
        }

        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
        ]);
        return response([
            'distance' => $driver->distance,
            'qcode' => $qStatus
        ], 200);
    }

    public function checkStatus($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();

        return response($driver->busy, 200);
    }
    public function join($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $service = Service::where('vehicle_id', $driver->vehicle_id)->firstOrFail();
        $service->drivers()->attach($driver->id);
        return response(2, 200);
    }
    public function detach($hash)
    {

        $driver = Driver::where('hash', $hash)->firstOrFail();
        $service = Service::where('vehicle_id', $driver->vehicle_id)->firstOrFail();
        $service->drivers()->detach($driver->id);
        return response(1, 200);
    }

    public function getDriverFromHash($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $office = User::find($driver->user_id);
        return response(['driver' => $driver, 'office' => $office], 200);
    }

    public function toggle($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $order = Order::where('driver_id', $driver->id)->whereIn('status', [2, 21])->count();
        if ($order == 0) {
            if ($driver->busy == 0) {
                $driver->busy = 2;
            } elseif ($driver->busy == 2) {
                $driver->busy = 0;
            }
            $driver->save();
            Stream::create([
                'pid' => $driver->id,
                'model' => 'Driver',
                'action' => 'U',
                'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
            ]);
        }

        return response($driver->busy, 200);
    }
    public function reset($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();
        $driver->busy == 0;
        $driver->save();
        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
        ]);
        return response($driver->busy, 200);
    }

    private function orderMetric($coordinate, $oldDistance)
    {

        $distance = $oldDistance +
            (cooDistance($coordinate['oldLat'], $coordinate['oldLng'], $coordinate['newLat'], $coordinate['newLng']) * 1000);
        return round($distance, 0);
    }

    public function nearby($office, $lat, $lng, $dlat, $dlng,  $service)
    {
        $est_distance = 0;
        $est_time = 0;
        $jest_distance = 0;
        $jest_time = 0;
        $jest_price = 0;

        $office = User::findOrFail($office);
        $service = Service::findOrFail($service);

        $vehicle_id = $service->vehicle_id;
        $workRange = $office->settings['work_rang'];
        //$driver = DB::select('SELECT *, ( 3959 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance FROM drivers where user_id=? AND busy=?  HAVING distance < ? ORDER BY  distance ASC LIMIT 1', [$lat, $lng, $lat, $office->id, 2, $workRange]);
        //$driver = DB::select('SELECT *, ( 3959 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance FROM drivers where user_id=? AND busy=? AND vehicle_id=? HAVING distance < ? ORDER BY  distance ASC LIMIT 1', [$lat, $lng, $lat, $office->id, 2, $vehicle_id, $workRange]);
        $driver = DB::select('SELECT *, ( 3959 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance FROM drivers where user_id=? AND busy=? AND vehicle_id=?  ORDER BY  distance ASC LIMIT 1', [$lat, $lng, $lat, $office->id, 2, $vehicle_id]);

        if (count($driver) == 0) {
            return [];
        }
        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'key' => 'AIzaSyBBygkRzIk31oyrn9qtVvQmxfdy-Fhjwz0',
            'language' => 'en-US',
            'mode' => 'DRIVING',
            'origins' => $driver[0]->lat . ',' . $driver[0]->lng,
            'destinations' => $lat . ',' . $lng,
        ]);

        if ($response['status'] == 'OK' && $response['rows'][0]['elements'][0]['status'] == 'OK') {
            $est_distance = $response['rows'][0]['elements'][0]['distance']['value'];
            $est_time = $response['rows'][0]['elements'][0]['duration']['value'];
        }

        //Est Price
        if ($service->plan == 'TRACK' || $service->plan == 'DRIVER') {
            $est = $this->est_stuff($lat, $lng, $dlat, $dlng);

            $jest_distance = $est['distance'];
            $jest_time = $est['time'];
            $jest_price = (($jest_distance / 1000) * $service->distance) + (($jest_time / 60) * $service->time) + $service->const;
        }

        return [
            'driver' => $driver[0],
            'distance' => $est_distance,
            'time' => $est_time,
            'estDistance' => $jest_distance,
            'estTime' => $jest_time,
            'estPrice' => round($jest_price, 2)
        ];
    }

    private function est_stuff($lat, $lng, $dlat, $dlng)
    {
        $data = [];
        if ($lat != 0 && $lng != 0   && $dlat != 0  && $dlng != 0) {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'key' => 'AIzaSyBBygkRzIk31oyrn9qtVvQmxfdy-Fhjwz0',
                'language' => 'en-US',
                'mode' => 'DRIVING',
                'origins' => $lat . ',' . $lng,
                'destinations' => $dlat . ',' . $dlng,
            ]);




            if ($response['status'] == 'OK' && $response['rows'][0]['elements'][0]['status'] == 'OK') {
                $data = [
                    'distance' => $response['rows'][0]['elements'][0]['distance']['value'],
                    'time' => $response['rows'][0]['elements'][0]['duration']['value'],
                ];
            }
        }
        return $data;
    }
}
