<?php
declare(strict_types = 1);

namespace Krasov\SeoManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class SeoManager
 *
 * @package Krasov\SeoManager\Facades
 */
class SeoManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function getFacadeAccessor(): string
    {
        return 'seomanager';
    }
}
