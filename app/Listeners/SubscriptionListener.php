<?php

namespace App\Listeners;

use App\Events\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SubscriptionListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Subscription $event): void
    {
        $userSubscription = $event->userSubscription;
         
        
    }
}
