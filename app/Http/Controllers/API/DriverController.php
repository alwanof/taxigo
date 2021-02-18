<?php

namespace App\Http\Controllers\API;

use App\Driver;
use App\Http\Controllers\Controller;
use App\Order;
use App\Parse\Stream;
use App\User;
use Illuminate\Http\Request;

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
            $order->save();
        }


        //Update driver coordinates
        $olat = $office->settings['coordinate_lat'];
        $olng = $office->settings['coordinate_lng'];
        $distance = cooDistance($olat, $olng, $lat, $lng);
        $driver->lat = $lat;
        $driver->lng = $lng;
        $driver->distance = $distance;
        $driver->save();



        Stream::create([
            'pid' => $driver->id,
            'model' => 'Driver',
            'action' => 'U',
            'meta' => ['hash' => $driver->hash, 'office' => $driver->user_id, 'agent' => $driver->parent]
        ]);
        return response(1, 200);
    }

    public function checkStatus($hash)
    {
        $driver = Driver::where('hash', $hash)->firstOrFail();

        return response($driver->busy, 200);
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
