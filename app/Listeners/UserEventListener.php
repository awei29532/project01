<?php

namespace App\Listeners;

use App\Events\UserEvent;
use App\Models\UserLog;

class UserEventListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 處理事件。
     *
     * @param  UserOperation  $evt
     * @return void
     */
    public function handle(UserEvent $evt)
    {
        // 使用 $evt
        UserLog::create([
            'user_id' => $evt->user->id ?? 0,
            'event'   => $evt->event,
            'content' => $evt->array,
            'ip'      => $evt->ip,
            'device'  => $evt->device
        ]);
    }

}