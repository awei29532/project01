<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
	use HasRoles;
	
	const TYPE_ACCOUNTANT = 0;
	const TYPE_ADMIN = 1;
	const TYPE_AGENT = 2;

	protected $table = 'user';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token', 'lastpwdchanged'
	];

	protected $m_agentList = null;
	
	public function agent()
	{
		return $this->hasOne(Agent::class, 'id', 'agent_id');
	}
}
