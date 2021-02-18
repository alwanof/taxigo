<?php

use App\Driver;
use App\Notifications\SendCredentials;
use App\Order;
use App\Parse\User as ParseUser;
use App\Role;
use App\Service;
use App\Setting;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Parziphal\Parse\Auth\UserModel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {


    return view('welcome');
});

Route::get('/test', function () {
    $order = Order::where('driver_id', 6)
        ->where('status', 22)
        ->first();
    if ($order) {
        return 'ok';
    }
    return 99;
});

Route::get('/set/lang/{lang}', 'ClientController@setLang')->name('client.lang');

Route::get('/taxi/{office_email}', 'ClientController@index')->name('client.create');
Route::post('/taxi/dist/order', 'ClientController@dist')->name('client.dist');
Route::post('/taxi/composse/order', 'ClientController@composse')->name('client.composse');


Route::get('jobs/go/{m}', function ($m) {
    $jobs = [];

    $noResD = $m;
    $noResD_date = new DateTime;
    $noResD_date->modify('-' . $noResD . ' minutes');
    $noResD_formatted_date = $noResD_date->format('Y-m-d H:i:s');

    $orders = Order::where('status', 2)
        ->where('updated_at', '<=', $noResD_formatted_date)
        ->get();

    foreach ($orders as $key => $order) {
        $order->driver_id = null;
        $order->block = ($order->block == null) ? $order->driver_id : '--' . $order->driver_id;
        $order->save();
        $block = explode('--', $order->block);
        $driver = Driver::where('user_id', $order->user_id)
            ->where('busy', 0)
            ->whereNotIn('id', $block)
            ->inRandomOrder()
            ->first();
        if ($driver) {
            Http::get(env('APP_URL') . '/api/order/office/select/' . $driver->id . '/to/' . $order->id);
            $jobs[] = $order;
        }
    }
    return $jobs;
});

Route::get('jobs/nores/{m}', function ($m) {

    $noResD = $m;
    $noResD_date = new DateTime;
    $noResD_date->modify('-' . $noResD . ' minutes');
    $noResD_formatted_date = $noResD_date->format('Y-m-d H:i:s');

    $orders = Order::whereIn('status', [0, 1, 12, 2, 21, 3])
        ->where('updated_at', '<=', $noResD_formatted_date)
        ->update(['status' => 95]);
    Driver::where('busy', 1)->whereHas('orders', function ($q) {
        $q->whereNotIn('status', [2, 21]);
    })->update(['busy' => 0]);


    return $orders;
});
