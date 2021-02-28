<?php

namespace App\Http\Controllers;

use App\Driver;
use App\Order;
use App\Parse\Stream;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{

    private function getLang($lang)
    {
        $locale = $lang;
        if (session()->has('lang')) {
            App::setLocale(session()->get('lang'));
            $locale = session()->get('lang');
        } else {
            session()->put('lang', $lang);
            App::setLocale($lang);
        }

        return $locale;
    }

    public function setLang($lang)
    {

        session()->put('lang', $lang);
        App::setLocale($lang);
        return back();
    }


    private function sendMobileNoti($title, $body, $token)
    {

        $response = Http::withHeaders([
            'X-Parse-Application-Id' => 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
            'X-Parse-REST-API-Key' => 'ozmiEzNHJIAb3EqCD9lislhOC5dPsC0OS18DFJ6j',
            'Content-Type' => 'application/json'
        ])->post('https://parseapi.back4app.com/functions/gettoken', [
            'hash' => $token,
        ]);

        try {
            $SERVER_API_KEY = 'AAAAwpX5cTo:APA91bG5qS4xNQCAdOxn8N2tVhkFR7nHsk8smxNTgw-Lh-ceWtxuYXwdhsGadenH3wrrKsA96pg5KDu7cA9JssEyp_LjKA99xEYpernypzDbVFqqzLTO8BLpyALDLcnwAhNKCXmHCD4s';
            $data = [
                "registration_ids" => [
                    $response['result']['token']
                ],
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);
        } catch (\Throwable $th) {
            return 0;
        }
        return 1;
    }

    public function index($office_email)
    {



        $office = User::where('email', $office_email)->firstOrFail();

        $lang = $this->getLang($office->settings['lang']);

        if ($office->level != 2) abort(404);
        $agent = $office->parent;
        $session = session()->getId();

        $lang = $this->getLang($office->settings['lang']);
        //return App::getLocale();
        $mapCenter = [$office->settings['coordinate_lat'], $office->settings['coordinate_lng']];
        return view('client.form', compact(['office', 'agent', 'session', 'lang', 'mapCenter']));
    }

    public function composse(Request $request)
    {

        $hash = explode('%&', $request->hash);
        $office = User::findOrFail($hash[0]);
        $lang = $this->getLang($office->settings['lang']);
        $agent = User::findOrFail($hash[2]);
        $session = session()->getId();
        $oldOrder = Order::where('session', $session)
            ->whereNotIn('status', [9, 91, 92, 93, 94, 95, 99, 90])
            ->count();
        // Oops I'v found an old order running
        if ($oldOrder > 0) {
            $order = $oldOrder = Order::where('session', $session)->firstOrFail();
            return view('client.order', compact(['office', 'agent', 'order', 'lang']));
        }
        // Create a new Order

        $order = Order::create(
            [
                'session' => $request->session,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'from_address' => $request->from_address,
                'from_lat' => $request->from_lat,
                'from_lng' => $request->from_lng,
                'to_address' => ($request->to_address) ? $request->to_address : null,
                'to_lat' => ($request->to_lat || ($request->to_lat != $request->from_lat)) ? $request->to_lat : 0,
                'to_lng' => ($request->to_lng || ($request->to_lng != $request->from_lng)) ? $request->to_lng : 0,
                'service_id' => $request->service_id,
                'user_id' => $office->id,
                'parent' => $agent->id,
                'status' => 0,
                'note' => $request->note
            ]
        );
        $this->est_stuff($order);
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
        return view('client.order', compact(['office', 'agent', 'order', 'lang']));
    }

    private function est_stuff($order)
    {
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
        return true;
    }

    private function forwardOrder($order)
    {
        if ($order->office->settings['auto_fwd_order']) {

            $workRange = $order->office->settings['work_rang'];
            $drivers = DB::select('SELECT *, ( 3959 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance FROM drivers where user_id=? AND busy=? HAVING distance < ?', [$order->from_lat, $order->from_lng, $order->from_lat, $order->user_id, 2, $workRange]);
            $driverIDs = array_map(function ($value) {
                return $value->id;
            }, $drivers);
            $driverHashs = array_map(function ($value) {
                return $value->hash;
            }, $drivers);

            $order->subscribers()->sync($driverIDs);
            $order->status = 13;
            $order->save();

            return $driverHashs;
        }
        return false;
    }


    public function dist(Request $request)
    {
        $hash = explode('%&', $request->hash);

        $office = User::findOrFail($hash[0]);
        $agent = User::findOrFail($hash[2]);
        $order = $request->all();
        $lang = $this->getLang($office->settings['lang']);
        $mapCenter = [$office->settings['coordinate_lat'], $office->settings['coordinate_lng']];
        return view('client.dist', compact(['office', 'agent', 'order', 'lang', 'mapCenter']));
    }
}
