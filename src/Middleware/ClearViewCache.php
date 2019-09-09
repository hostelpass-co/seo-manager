<?php
declare(strict_types = 1);

namespace Krasov\SeoManager\Middleware;

use Closure;
use Illuminate\Support\Facades\Artisan;

/**
 * Class ClearViewCache
 *
 * @package Krasov\SeoManager\Middleware
 */
class ClearViewCache
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
        Artisan::call('view:clear');

        return $next($request);
    }
}
