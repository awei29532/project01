<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderConfig extends Model
{
    protected $table = 'provider_config';

    public function provider()
    {
        return $this->belongsTo('App\Models\Provider', 'provider_id');
    }
}
