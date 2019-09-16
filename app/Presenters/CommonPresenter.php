<?php

namespace App\Presenters;

class CommonPresenter
{

    public function __construct()
    {

    }

    public function getLangPacks($input)
    {
        $langs = [];
        if (!is_array($input)) {
            $langs[$input] = __($input);
        } else {
            foreach ($input as $name) {
                $langs[$name] = __($name);
            }
        }
        return $langs;
    }

}