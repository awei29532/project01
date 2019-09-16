<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wager extends Model
{
    //define constant
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 0;

    protected $table = 'wager';

    public function agent()
    {
        return $this
            ->hasOne('App\Models\Agent', 'id', 'agent_id')
            ->select('id', 'username');
    }

    public function account()
    {
        return $this
            ->hasOne('App\Models\Account', 'id', 'account_id')
            ->select('id', 'username');
    }

    public function provider()
    {
        return $this
            ->hasOne('App\Models\Provider', 'id', 'provider_id')
            ->select('id', 'name');
    }
}
