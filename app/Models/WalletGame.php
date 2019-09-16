<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletGame extends Model
{
	protected $table = 'wallet_game';
	public $timestamps = false;

	protected $account = false;

	public function agent()
	{
		return $this
			->hasOne('App\Models\Agent', 'id', 'agent_id')
			->select('id', 'username');
	}

	public function account()
	{
		$this->account = $this
			->hasOne('App\Models\Account', 'id', 'account_id')
			->select('id', 'agent_id', 'username');
		return $this->account;
	}

	public function getProvider()
	{
		return $this
			->hasOne('App\Models\Provider', 'id', 'provider')
			->select('id', 'name');
	}
}
