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
// TEST
Route::get('/test', 'TestoController@test')->name('test.index');
Route::post('/test', 'TestoController@create')->name('test.create');
Route::get('/test/reset', 'TestoController@reset')->name('test.reset');
Route::get('/test/office/accept', 'TestoController@officeAccept')->name('test.office.accept');
Route::get('/test/office/reject', 'TestoController@officeReject')->name('test.office.reject');
Route::get('/test/front/accept', 'TestoController@frontAccept')->name('test.front.accept');
Route::get('/test/front/reject', 'TestoController@frontReject')->name('test.front.reject');
Route::get('/test/driver/accept', 'TestoController@driverAccept')->name('test.driver.accept');
Route::get('/test/driver/reject', 'TestoController@driverReject')->name('test.driver.reject');

Route::post('/test/driver/tracking', 'TestoController@tracking')->name('test.tracking');

Route::post('/test/send/offer', 'TestoController@sendOffer')->name('test.send.offer');

Route::post('/test/driver/take/order', 'TestoController@driverTakeOrder')->name('test.driver.take.order');

Route::get('/test/order/start', 'TestoController@orderStart')->name('test.order.start');
Route::get('/test/order/end', 'TestoController@orderEnd')->name('test.order.end');
Route::get('/test/order/abort', 'TestoController@orderAbort')->name('test.order.abort');
Route::post('/test/order/complete', 'TestoController@orderComplete')->name('test.order.complete');

// end Test

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
