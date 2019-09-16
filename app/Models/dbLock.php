<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class dbLock extends Model
{
    protected $table = 'locker';

    public $timestamps = false;

    protected $guarded = ['id', 'status'];

    protected $casts = [
        'status' => 'int',
    ];
}
