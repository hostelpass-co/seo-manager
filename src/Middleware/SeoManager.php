<?php
declare(strict_types = 1);

namespace Krasov\SeoManager\Middleware;

use Closure;

/**
 * Class SeoManager
 *
 * @package Krasov\SeoManager\Middleware
 */
class SeoManager
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \View::share('metaData', metaData());

        return $next($request);
    }
}
