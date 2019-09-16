<?php

namespace App\Events;

use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserEvent
{

    use SerializesModels;

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var User
     */
    public $user;
    /**
     * @var string
     */
    public $event = 'undefined';
    /**
     * @var array
     */
    public $array = null;
    /**
     * @var string
     */
    public $ip = '0.0.0.0';
    /**
     * @var string
     */
    public $device = 'unknow';
    /**
     * Create a new event instance.
     * @param  Request $request
     * @param  string  $event
     * @param  array $array
     * @return void
     */
    public function __construct(Request $request, string $event, $array = null)
    {
        $this->request = $request;
        $this->user    = $request->user();
        $this->ip      = $request->ip();
        $this->device  = $request->userAgent();
        $this->event   = $event;
        $this->array   = $array;
    }
}
