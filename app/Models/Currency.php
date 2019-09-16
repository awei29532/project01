<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
	//define constant
	const STATUS_ACTIVE = 1;
	const STATUS_SUSPENDED = 0;

	protected $table = 'currency';

	public static function getAllAsOptions($activeOnly = true, $prepend = false)
	{
		$options = [];

		if ($prepend) {
			$options = ['' => $prepend];
		}

		$condition = '1';
		if ($activeOnly) $condition .= ' AND status = ' . self::STATUS_ACTIVE;
		if ($currencies = self::whereRaw($condition)->get()) {
			foreach ($currencies as $currency) {
				$options[$currency->code] = $currency->code;
			}
		}
		return $options;
	}

	public static function getStatusOptions()
	{
		$options = [
			0 => trans('common.suspended'),
			1 => trans('common.active'),
		];

		return $options;
	}
}
