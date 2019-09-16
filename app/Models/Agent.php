<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $parent_id
 * @property string $username
 * @property string $currency
 * @property string $key
 * @property string $secret
 * @property int $wallet_mode
 * @property int $auth_mode
 * @property string $prefix
 * @property string $callback
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 */
class Agent extends Model
{
	protected $table = 'agent';

	public function detail()
	{
		return $this->hasOne('App\Models\AgentDetail', 'agent_id');
	}

	public function settings()
	{
		return $this->hasMany('App\Models\AgentSetting', 'agent_id');
	}

	public function members()
	{
		return $this->hasMany('App\Models\Account', 'agent_id');
	}

	public function wallet()
	{
		return $this->hasOne(AgentWallet::class, 'agent_id');
	}

	public static function getUpline($id, &$uplines)
	{
		if ($agent = Agent::find($id)) {
			$uplines[$agent->level] = $id;
			if ($agent->parent_id > 0) {
				return self::getUpline($agent->parent_id, $uplines);
			}
		}

		return;
	}
}
