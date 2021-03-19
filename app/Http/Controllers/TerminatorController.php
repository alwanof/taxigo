<?php

namespace App\Http\Controllers;

use App\Driver;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Order;

class TerminatorController extends Controller
{
    public function clear()
    {
        $orderInt = 23; //hour
        $order2Int = 4; // min
        $order3Int = 5; //min
        $this->clearOrders($orderInt);
        $this->clear2Orders($order2Int);
        $this->clear3Orders($order3Int);
    }

    private function clearOrders($interval)
    {
        $orders = Order::where('created_at', '<', Carbon::now()->subHours($interval)->toDateTimeString())
            ->whereNotIn('status', [9, 90, 91, 92, 93, 94, 99])
            ->update(['status' => 99]);
        return $orders;
    }
    private function clear2Orders($interval)
    {
        $orders = Order::where('created_at', '<', Carbon::now()->subMinutes($interval)->toDateTimeString())
            ->where('status', 2)
            ->get();
        foreach ($orders as $order) {
            Driver::where('id', $order->driver_id)
                ->update(['busy' => 2]);
        }
        $orders = Order::where('created_at', '<', Carbon::now()->subMinutes($interval)->toDateTimeString())
            ->where('status', 2)
            ->update(['status' => 99]);
        return $orders;
    }
    private function clear3Orders($interval)
    {
        $orders = Order::where('created_at', '<', Carbon::now()->subMinutes($interval)->toDateTimeString())
            ->where('status', 3)
            ->update(['status' => 94]);
        return $orders;
    }
}
