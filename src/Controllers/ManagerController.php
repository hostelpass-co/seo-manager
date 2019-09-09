<?php
declare(strict_types = 1);

namespace Krasov\SeoManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Krasov\SeoManager\Models\SeoManager as SeoManagerModel;
use Krasov\SeoManager\Models\Translate;
use Krasov\SeoManager\Traits\SeoManagerTrait;

/**
 * Class ManagerController
 *
 * @package Krasov\SeoManager
 */
class ManagerController extends Controller
{
    use SeoManagerTrait;

    protected $locale;

    public function __construct()
    {
        if (Request::input('locale')) {
            app()->setLocale(Request::input('locale'));
            $this->locale = app()->getLocale();
        }
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('seo-manager::index');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoutes(): JsonResponse
    {
        $routes = SeoManagerModel::all();

        return response()->json(['routes' => $routes]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getModels(): JsonResponse
    {
        try {
            $models = $this->getAllModels();

            return response()->json(['models' => $models]);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getModelColumns(Request $request): JsonResponse
    {
        try {
            $model = $request->get('model');
            $columns = $this->getColumns($model);

            return response()->json(['columns' => $columns]);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeData(Request $request): JsonResponse
    {
        $allowedColumns = Schema::getColumnListing(config('seo-manager.database.table'));
        try {
            $id = $request->get('id');
            $type = $request->get('type');
            $seoManager = SeoManagerModel::find($id);
            if (in_array($type, $allowedColumns, true)) {
                $data = $request->get($type);
                if ($type !== 'mapping' && $this->locale !== config('seo-manager.locale')) {
                    $translate = $seoManager->translation()->where('locale', $this->locale)->first();
                    if (!$translate) {
                        $newInst = new Translate();
                        $newInst->locale = $this->locale;
                        $translate = $seoManager->translation()->save($newInst);
                    }
                    $translate->$type = $data;
                    $translate->save();
                } else {
                    $seoManager->$type = $data;
                    $seoManager->save();
                }
            }

            return response()->json([$type => $seoManager->$type]);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExampleTitle(Request $request): JsonResponse
    {
        try {
            $manager = SeoManagerModel::find($request->id);
            $titles = $request->get('title_dynamic');
            $exampleTitle = $this->getDynamicTitle($titles, $manager);

            return response()->json(['example_title' => $exampleTitle]);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRoute(Request $request): JsonResponse
    {
        try {
            SeoManagerModel::destroy($request->id);

            return response()->json(['deleted' => true]);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     *
     * @return array|null
     */
    public function sharingPreview(Request $request): ?array
    {
        $id = $request->get('id');
        $seoManager = SeoManagerModel::find($id);

        if ($seoManager === null) {
            return null;
        }

        $ogData = $this->getOgData($seoManager, null);

        return response()->json(['og_data' => $ogData]);
    }
}
