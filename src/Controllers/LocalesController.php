<?php
declare(strict_types = 1);

namespace Krasov\SeoManager;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Krasov\SeoManager\Models\Locale;

/**
 * Class LocalesController
 *
 * @package Krasov\SeoManager
 */
class LocalesController extends Controller
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocales(): JsonResponse
    {
        $locales = Locale::pluck('name');

        return response()->json(['locales' => $locales]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLocale(Request $request): JsonResponse
    {
        try {
            $locale = Locale::whereName($request->get('name'))->first();
            if (!$locale) {
                $locale = new Locale();
                $locale->fill($request->all());
                $locale->save();

                return response()->json(['locale' => $locale->name]);
            }

            throw new Exception('Locale is already exist');
        } catch (Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 400);
        }
    }
}
