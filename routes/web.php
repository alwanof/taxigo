<?php

use App\Driver;
use App\Order;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;


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

Route::group(['middleware' => 'lang'], function () {
    Route::get('/', function () {
        $lang = session()->get('lang') ?? 'en';
        $path = "storage/json/" . $lang . ".json"; // ie: /var/www/laravel/app/storage/json/filename.json
        if (!File::exists($path)) {
            throw new Exception("Invalid Data File");
        }
        $data = json_decode(
            File::get($path),
            true
        ); // string
        return view('templates.default.home', compact('data', 'lang'));
    });

    Route::get('/page/{slug}', function ($slug) {
        $lang = session()->get('lang') ?? 'en';
        $path = "storage/json/" . $lang . ".json"; // ie: /var/www/laravel/app/storage/json/filename.json
        if (!File::exists($path)) {
            throw new Exception("Invalid Data File");
        }
        $data = json_decode(File::get($path), true); // string
        if (!array_key_exists($slug, $data['pages'])) {
            abort(404);
        }
        return view('templates.default.page', compact('data', 'lang', 'slug'));
    });

    Route::get('/contact', function () {
        $lang = session()->get('lang') ?? 'en';
        $path = "storage/json/" . $lang . ".json"; // ie: /var/www/laravel/app/storage/json/filename.json
        if (!File::exists($path)) {
            throw new Exception("Invalid Data File");
        }
        $data = json_decode(File::get($path), true); // string

        return view('templates.default.contact', compact('data', 'lang'));
    });
});
Route::get('/foo', function (Request $request) {
    $response = Http::get('http://ip-api.com/php/24.48.0.1');
    $users = User::all();
    $filteredArray = Arr::where($users->toArray(), function ($value, $key) {
        return $value['settings']['country'] == 'tr';
    });
    return $filteredArray;
});

Route::get('/ip', function (Request $request) {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip); // just to be safe
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return request()->ip(); // it will return server ip when no client ip found
});
// TEST

Route::get('/loaderio-7c0679154a4aa40544f62c84da1ffa48', function () {

    echo 'loaderio-7c0679154a4aa40544f62c84da1ffa48';
});
Route::get('/move/{s}/{hash?}', 'TestoController@move');

Route::get('/test', 'TestoController@test')->name('test.index');

Route::post('/test', 'TestoController@create')->name('test.create');
Route::get('/test/reset', 'TestoController@reset')->name('test.reset');
Route::get('/test/office/accept', 'TestoController@officeAccept')->name('test.office.accept');
Route::get('/test/office/reject', 'TestoController@officeReject')->name('test.office.reject');
Route::get('/test/front/accept', 'TestoController@frontAccept')->name('test.front.accept');
Route::get('/test/front/reject', 'TestoController@frontReject')->name('test.front.reject');
Route::get('/test/driver/accept/{driver_id}', 'TestoController@driverAccept')->name('test.driver.accept');
Route::get('/test/driver/reject', 'TestoController@driverReject')->name('test.driver.reject');

Route::post('/test/driver/tracking', 'TestoController@tracking')->name('test.tracking');
Route::get('/test/{hash}/join', 'TestoController@join')->name('test.join');
Route::get('/test/{hash}/detach', 'TestoController@detach')->name('test.detach');

Route::post('/test/send/offer', 'TestoController@sendOffer')->name('test.send.offer');

Route::post('/test/driver/take/order', 'TestoController@driverTakeOrder')->name('test.driver.take.order');

Route::get('/test/order/start', 'TestoController@orderStart')->name('test.order.start');
Route::get('/test/order/end', 'TestoController@orderEnd')->name('test.order.end');
Route::get('/test/order/abort', 'TestoController@orderAbort')->name('test.order.abort');
Route::post('/test/order/complete', 'TestoController@orderComplete')->name('test.order.complete');

// end Test
Route::get('/clear', 'TerminatorController@clear')->name('clear');
Route::get('/set/lang/{lang}', 'ClientController@setLang')->name('client.lang');

Route::get('/taxi/{office_email?}', 'ClientController@index')->name('client.create');
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
