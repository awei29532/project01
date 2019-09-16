<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSetting extends Model
{
    protected $table = 'agent_setting';

    public function agent()
    {
        return $this->belongsTo('App\Models\Agent', 'agent_id');
    }

    public function provider()
    {
        return $this->hasOne(Provider::class, 'id', 'provider_id');
    }
}
