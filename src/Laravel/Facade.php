<?php

namespace Transmission\Laravel;

use Transmission\Client;

/**
 * Class Facade
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
