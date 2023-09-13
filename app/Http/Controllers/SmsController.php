<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OsonSMS\SMSGateway\SMSGateway;

class SmsController extends Controller
{
    public function sendSms()
    {
        $txn_id = uniqid();
        $result = SMSGateway::Send('931272616', 'This is my test message from Laravel!', $txn_id);

        if ($result) {
            return "SMS has been sent successfully";
        } else {
            return "An error occurred while sending the SMS";
        }
    }
}
