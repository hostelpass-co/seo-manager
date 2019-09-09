<?php
declare(strict_types = 1);

namespace Krasov\SeoManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Krasov\SeoManager\Traits\SeoManagerTrait;

/**
 * Class ImportController
 *
 * @package Krasov\SeoManager
 */
class ImportController extends Controller
{
    use SeoManagerTrait;

    /**
     * Import routes to the SeoManager database table
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function __invoke(): JsonResponse
    {
        try {
            $routes = $this->importRoutes();
            return response()->json(['routes' => $routes]);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }
    }
}
