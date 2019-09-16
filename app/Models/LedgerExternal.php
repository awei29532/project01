<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerExternal extends Model
{
    protected $table = 'ledger_external';

    public function agent()
    {
        return $this->hasOne('App\Models\Agent', 'id', 'agent_id');
    }

    public function account()
    {
        return $this->hasOne('App\Models\Account', 'id', 'account_id');
    }

    public function provider()
    {
        return $this->hasOne('App\Models\Provider', 'id', 'provider_id');
    }
}
