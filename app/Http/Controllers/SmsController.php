<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SmsVerification;
use App\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OsonSMS\SMSGateway\SMSGateway;

class SmsController extends Controller
{
    public function sendSms()
    {
        
    }
}
