<?php

namespace Techbeansjp\LaravelDbConfig\App\Facades;

use Illuminate\Support\Facades\Facade;

class DbConfigFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dbconfig';
    }
}