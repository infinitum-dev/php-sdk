<?php

namespace Fyi\Infinitum\Facades;

use Illuminate\Support\Facades\Facade;

class Infinitum extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'infinitum';
    }
}
