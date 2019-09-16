<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $ref_id
 * @property int $provider_id
 * @property int $agent_id
 * @property int $account_id
 * @property string $currency
 * @property double $amount
 * @property double $amount_final
 * @property double $end_balance
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 */
class Ledger extends Model
{
    //define constant
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 0;

    protected $table = 'ledger';

    public function agent()
    {
        return $this
            ->hasOne('App\Models\Agent', 'id', 'agent_id')
            ->select('id', 'username');
    }

    public function account()
    {
        return $this
            ->hasOne('App\Models\Account', 'id', 'account_id')
            ->select('id', 'username', 'nickname');
    }

    public function provider()
    {
        return $this
            ->hasOne('App\Models\Provider', 'id', 'provider_id')
            ->select('id', 'name');
    }
}
