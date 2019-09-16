<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Slot extends Model
{
	protected $table = 'slot';

	public function names($providers = [], $gids = [])
	{
		$games = self::select('name', 'namecn', DB::raw('CONCAT( provider_id , "_" , game_id ) AS game_code'));
		if (count($providers)) {
			$games = $games->whereIn('provider_id', $providers);
		}
		if (count($gids)) {
			$games = $games->whereIn('game_id', $gids);
		}
		$games   = $games->get();
		$results = [];
		foreach ($games as $game) {
			$results[$game['game_code']] = [
				'name'    => $game['name'],
				'name_cn' => $game['namecn']
			];
		}
		return $results;
	}

	public function provider()
	{ 
		return $this->hasOne(Provider::class, 'id', 'provider_id');
	}
}
