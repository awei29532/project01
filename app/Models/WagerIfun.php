<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WagerIfun extends Model
{
	//define constant
	const STATUS_ACTIVE = 1;
	const STATUS_SUSPENDED = 0;

	protected $table = 'wager_ifun';
}
