<?php

namespace Laraditz\Experian;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Laraditz\Experian\Skeleton\SkeletonClass
 */
class ExperianFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'experian';
    }
}
