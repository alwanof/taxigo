<?php

namespace App\Http\Controllers;

use App\Driver;
use App\Order;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

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

        return view('client.form', compact(['office', 'agent', 'session', 'lang']));
    }

    public function composse(Request $request)
    {

        $hash = explode('%&', $request->hash);
        $office = User::findOrFail($hash[0]);
        $agent = User::findOrFail($hash[2]);
        $session = session()->getId();
        $oldOrder = Order::where('session', $session)
            ->whereNotIn('status', [9, 91, 92, 93, 94, 95, 99])
            ->count();
        if ($oldOrder > 0) {

            $order = $oldOrder = Order::where('session', $session)->firstOrFail();
            $lang = $this->getLang($office->settings['lang']);
            return view('client.order', compact(['office', 'agent', 'order', 'lang']));
        } else {

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
                    'to_lat' => ($request->to_lat) ? $request->to_lat : null,
                    'to_lng' => ($request->to_lng) ? $request->to_lng : null,
                    'user_id' => $office->id,
                    'parent' => $agent->id,
                    'status' => 0,
                    'note' => $request->note
                ]
            );
        }

        if ($office->settings['auto_fwd_order']) {
            $block = explode('--', $order->block);
            $driver = Driver::where('user_id', $order->user_id)
                ->where('busy', 0)
                ->whereNotIn('id', $block)
                ->inRandomOrder()
                ->first();
            if ($driver) {
                $order->driver_id = $driver->id;
                $order->status = 2;
                $order->save();
            } else {
                $order->status = 91;
                $order->save();
            }
            $response = Http::withHeaders([
                'X-Parse-Application-Id' => 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
                'X-Parse-REST-API-Key' => 'ozmiEzNHJIAb3EqCD9lislhOC5dPsC0OS18DFJ6j',
                'Content-Type' => 'application/json'
            ])->post('https://parseapi.back4app.com/functions/stream', [
                'pid' => $order->id,
                'model' => 'Order',
                'action' => 'C',
                'meta' => ['hash' => $driver->hash, 'office' => $office->id, 'agent' => $agent->id, 'action' => 'create']

            ]);
            $this->sendMobileNoti('New Order!', 'You have been got a new order', $driver->hash);
        } else {
            $response = Http::withHeaders([
                'X-Parse-Application-Id' => 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
                'X-Parse-REST-API-Key' => 'ozmiEzNHJIAb3EqCD9lislhOC5dPsC0OS18DFJ6j',
                'Content-Type' => 'application/json'
            ])->post('https://parseapi.back4app.com/functions/stream', [
                'pid' => $order->id,
                'model' => 'Order',
                'action' => 'C',
                'meta' => ['office' => $office->id, 'agent' => $agent->id, 'action' => 'create']

            ]);
        }


        $lang = $this->getLang($office->settings['lang']);
        return view('client.order', compact(['office', 'agent', 'order', 'lang']));
    }


    public function dist(Request $request)
    {
        $hash = explode('%&', $request->hash);

        $office = User::findOrFail($hash[0]);
        $agent = User::findOrFail($hash[2]);
        $order = $request->all();
        $lang = $this->getLang($office->settings['lang']);
        return view('client.dist', compact(['office', 'agent', 'order', 'lang']));
    }
}
