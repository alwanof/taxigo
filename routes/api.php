<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/testoo', function () {
});
Route::get('/drivers/{user}', 'API\DriverController@getUserDrivers');

Route::get('fetch/drivers/{driver}', 'API\DriverController@getDriver');

Route::get('/orders/{id}', 'API\OrderController@getUserOrders');

Route::post('/orders/create', 'API\OrderController@create');

Route::post('/orders/update', 'API\OrderController@update');

Route::post('/orders/delete', 'API\OrderController@trash');

Route::get('/orders/get/{order}', 'API\OrderController@getOrder');

Route::get('/orders/cancel/{order}', 'API\OrderController@cancel');

Route::get('/order/office/reject/{order}', 'API\OrderController@reject');

Route::get('/order/office/undo/{order}', 'API\OrderController@undo');

Route::get('/order/customer/reject/{order}', 'API\OrderController@customerReject');

Route::get('/order/office/approve/{order}', 'API\OrderController@approve');

Route::get('/order/customer/approve/{order}', 'API\OrderController@customerApprove');

Route::get('/order/office/select/{driver}/to/{order}', 'API\OrderController@selectDriver');

Route::get('/order/office/send/{offer}/to/{order}', 'API\OrderController@sendOffer');



// API Mobile APP:

Route::get('/app/get/order/{hash}', 'API\OrderController@getDriverOrder');

Route::get('/app/approve/{order_id}', 'API\OrderController@driverApproveOrder');

Route::get('/app/{hash}/reject/{order_id}', 'API\OrderController@driverRejectOrder');

Route::get('/app/{hash}/done/{order_id}', 'API\OrderController@driverCompleteOrder');

Route::get('/app/{hash}/tracking/{lat}/{lng}', 'API\DriverController@tracking');

Route::get('/app/{hash}/check/active', 'API\DriverController@checkStatus');

Route::get('/app/{hash}/get/driver', 'API\DriverController@getDriverFromHash');

Route::get('/app/{hash}/toggle', 'API\DriverController@toggle');

Route::get('/app/{hash}/reset', 'API\DriverController@reset');
