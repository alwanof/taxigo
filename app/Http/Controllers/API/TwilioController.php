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
        $account_sid = 'AC322a366341630b5cc8978694acc590a7';

        $auth_token = 'cdb9e6f0dac5d5446ae04eb44f86c009';

        $twilio_number = "+14084795512";

        $client = new Client($account_sid, $auth_token);

        $verification_code = mt_rand(100000, 999999);

        $client->messages->create(
            $phone_number,
            array(
                'from' => $twilio_number,
                'body' => 'Welcome To ' . env('APP_NAME') . ' , Your Verfication Code is : ' . $verification_code
            )
        );

        return $verification_code;
    }
}
