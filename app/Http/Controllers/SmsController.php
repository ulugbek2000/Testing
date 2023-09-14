<?php

namespace App\Http\Controllers;

use App\Notifications\SmsVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OsonSMS\SMSGateway\SMSGateway;

class SmsController extends Controller
{
    public function sendSms()
    {
        Auth::user()->notify(new SmsVerification(['text' => 'Raqami yagonai shumo 9854', 'v_no' => 9854, 'number' => '931272616']));
        // $txn_id = uniqid();
        // $result = SMSGateway::Send('931272616', 'This is my test message from Laravel!', $txn_id);

        // if ($result) {
        //     return "SMS has been sent successfully";
        // } else {
        //     return "An error occurred while sending the SMS";
        // }
    }
}
