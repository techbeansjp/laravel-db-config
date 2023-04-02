<?php

namespace Techbeansjp\LaravelDatabaseConfiguration\App\Facades;

use Illuminate\Support\Facades\Facade;

class DbConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dbconfig';
    }
}