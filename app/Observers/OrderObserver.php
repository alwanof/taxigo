<?php

namespace App\Observers;

use App\Driver;
use App\Order;
use App\Parse\Stream;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the order "created" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        Log::info('This is some order information.');
        // calculating Estimated (Distance , Time , Price)
        /*if ($order->from_lat != 0 && $order->from_lng != 0   && $order->to_lat != 0  && $order->to_lng != 0) {
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


        //$response['rows'][0]['elements'][0];
        // Oh I found forward function is running
        if ($order->office->settings['auto_fwd_order']) {
            $this->forwardOrder($order->actor, $order->office, $order);
        }

        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'C',
            'meta' => ['office' => $order->office->id, 'agent' => $order->actor->id, 'action' => 'create']
        ]);*/
    }

    /**
     * Handle the order "updated" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        //
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        //
    }

    /**
     * Handle the order "restored" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function restored(Order $order)
    {
        //
    }

    /**
     * Handle the order "force deleted" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        //
    }

    private function forwardOrder($agent, $office, $order)
    {
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
        Stream::create([
            'pid' => $order->id,
            'model' => 'Order',
            'action' => 'C',
            'meta' => ['hash' => $driver->hash, 'office' => $office->id, 'agent' => $agent->id, 'action' => 'create']
        ]);

        $this->sendMobileNoti('New Order!', 'You have been got a new order', $driver->hash);
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
}
