<?php

namespace App\Services;

class HelperService
{

    public function __construct()
    {
        
    }

    public function getTypeName($type)
    {
        switch ($type) {
            case 1:
                return 'Normal';
            case 2:
                return 'Jackpot';
            case 3:
                return 'Misc';
            case 4:
                return 'Bonus';
        }
        return 'UnKnown';
    }

    public function hashInput($inputs, $secret)
    {
        ksort($inputs);
        $new_inputs = [];
        foreach ($inputs as $key => $value) {
            if (strlen($value) > 0) {
                $new_inputs[$key] = $value;
            }
        }
        $str = http_build_query($new_inputs);
        $new_inputs['hash'] = md5($str . $secret);
        return $new_inputs;
    }

    public function createGUID(): string {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $uuid = substr($charid, 0, 8)
            .substr($charid, 8, 4)
            .substr($charid,12, 4)
            .substr($charid,16, 4)
            .substr($charid,20,12);
        return $uuid;
    }
}
