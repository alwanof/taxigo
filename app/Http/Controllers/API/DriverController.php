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
use Illuminate\Support\Facades\Http;

class DriverController extends Controller
{
    public function getUserDrivers($user)
    {
        $user = User::findOrFail($user);
        switch ($user->level) {
            case 2:
                return Driver::where('user_id', $user->id)->where('busy', '>', 0)->get();
                break;
            case 1:
                return Driver::where('parent', $user->id)->where('busy', '>', 0)->get();
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
            $order->total = $order->total + $order->orderTotal($order->distance, $order->duration);

            //if ($order->service->plan != 'OFFER') {}

            $order->save();
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
                    Http::get(env('APP_URL') . '/api/' . $driver->hash . '/queue/detach')->json();
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
        $service->queues()->attach($driver->id);
        return response(2, 200);
    }
    public function detach($hash)
    {

        $driver = Driver::where('hash', $hash)->firstOrFail();
        $service = Service::where('vehicle_id', $driver->vehicle_id)->firstOrFail();
        $service->queues()->detach($driver->id);
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
}
