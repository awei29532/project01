<?php

namespace App\Listeners;

use App\Events\AgentEvent;
use App\Services\HelperService;
use Curl\Curl;

class AgentEventListener
{
    /**
     * 處理事件。
     *
     * @param  AgentOperation  $evt
     * @return void
     */
    public function handle(AgentEvent $evt)
    {
        $username = $evt->username;

        $helper = new HelperService();

        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('charset', 'UTF-8');
        $curl->post(
            env('API_URL') . '/admin/clear/agent_cache',
            json_encode($helper->hashInput([
                'username' => $username,
            ], env('API_ADMIN_SECRET')))
        );

        return response()->json(json_decode($curl->response));
    }
}
