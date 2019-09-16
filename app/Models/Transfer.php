<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfer';

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
}
