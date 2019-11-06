<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $agent_id
 * @property string $username
 * @property string $password
 * @property string $nickname
 * @property string $currency
 * @property double $balance
 * @property string $skey
 * @property int $status
 * @property int $data_ver
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class Account extends Model
{
	protected $table = 'account';

	public function agent()
	{
		return $this->hasOne(Agent::class, 'id', 'agent_id');
	}
}
