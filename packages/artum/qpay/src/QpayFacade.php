<?php

namespace Artum\Qpay;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Artum\Qpay\Skeleton\SkeletonClass
 */
class QpayFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'qpay';
    }
}
