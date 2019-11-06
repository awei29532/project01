<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class AgentEvent
{
    use SerializesModels;

    /**
     * @var string
     */
    public $username;

    /**
     * Create a new event instance.
     * @param  string  $username
     * @return void
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }
}
