<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
	//define constant
	const STATUS_ACTIVE = 1;
	const STATUS_SUSPENDED = 0;

	protected $table = 'provider';

	public function configs()
	{
		return $this->hasMany('App\Models\ProviderConfig', 'provider_id');
	}

	public function maps($status = null)
	{
		$providers = self::select('id', 'code', 'name');
		if ($status !== null) {
			$providers = $providers->where('status', '=', $status);
		}
		$providers = $providers->get();
		$results   = [];
		foreach ($providers as $provider) {
			$results[$provider['id']] = [
				'code' => $provider['code'],
				'name' => $provider['name']
			];
		}
		return $results;
	}
}
