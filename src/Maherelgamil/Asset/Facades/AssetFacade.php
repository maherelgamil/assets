<?php namespace Maherelgamil\Asset\Facades;

use Illuminate\Support\Facades\Facade;
use Maherelgamil\Asset\Asset;

class AssetFacade extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Asset::class ;
    }
}