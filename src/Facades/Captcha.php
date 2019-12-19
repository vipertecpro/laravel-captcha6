<?php

namespace Vipertecpro\Captcha\Facades;

use Illuminate\Support\Facades\Facade;

class Captcha extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor():string
    {
        return \Vipertecpro\Captcha\Captcha\Captcha::class;
    }
}
