<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $event
 * @property array $content
 * @property string $ip
 * @property string $device
 * @property string $created_at
 */
class UserLog extends Model
{

    protected $table = 'user_log';

    public $timestamps = false;
    
    protected $guarded = [
        'id', 'created_at'
    ];

    protected $casts = [
        'content' => 'array'
    ];

    public function user()
    {
        return $this->
            belongsTo(App\Models\User::class, 'id', 'user_id')
            ->select('id', 'username');
    }
}