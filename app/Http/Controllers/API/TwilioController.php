<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class TwilioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($phone_number)
    {
        $account_sid = env('TWILIO_ID', 'NNN');

        $auth_token = env('TWILIO_TOKEN', 'NNN');

        $twilio_number = "+17192154195";

        $client = new Client($account_sid, $auth_token);

        $verification_code = mt_rand(100000, 999999);

        $client->messages->create(
            $phone_number,
            array(
                'from' => $twilio_number,
                'body' => 'Welcome To ' . env('APP_NAME', 'Marasiel') . ' , Your Verfication Code is : ' . $verification_code
            )
        );

        return $verification_code;
    }
}
