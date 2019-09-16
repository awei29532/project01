<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentDetail extends Model
{
    protected $table = 'agent_detail';

    public function agent()
    {
        return $this->belongsTo('App\Models\Agent', 'agent_id');
    }
}
