<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueryLog extends Model
{
	protected $table = 'eloquent_querylog';

	public $timestamps = false;
}
