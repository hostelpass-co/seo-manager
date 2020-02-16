<?php
declare(strict_types = 1);

namespace Krasov\SeoManager\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Krasov\SeoManager\Models\SeoManager;
use Illuminate\Support\Facades\Schema;
use function in_array;
use function str_replace;

/**
 * Trait SeoManagerTrait
 *
 * @package Krasov\SeoManager\Traits
 */
trait SeoManagerTrait
{
    protected $exceptRoutes = [
        'api',
        'telescope',
        '_debugbar',
    ];

    protected $exceptColumns = [
        'password',
        'remember_token',
    ];

    public function __construct()
    {
        $this->exceptRoutes = array_merge($this->exceptRoutes, config('seo-manager.except_routes'));
        $this->exceptColumns = array_merge($this->exceptColumns, config('seo-manager.except_columns'));
    }

    /**
     * Detect Parameters from the URI
     * @param $uri
     *
     * @return mixed
     */
    private function getParamsFromURI($uri)
    {
        preg_match_all('/{(.*?)}/', $uri, $output_array);

        return $output_array[1];
    }

    /**
     * Check if the given data is URI param
     * @param $param
     *
     * @return bool
     */
    private function isParam($param): bool
    {
        $pattern_params = '/{(.*?)}/';
        preg_match_all($pattern_params, $param, $output_params);

        if (!empty($output_params[1])) {
            return true;
        }

        return false;
    }

    /**
     * Check if the given data is Title
     * @param $param
     *
     * @return bool
     */
    private function isTitle($param): bool
    {
        $pattern_title = '/~(.*?)~/';
        preg_match_all($pattern_title, $param, $pattern_title);

        if (!empty($pattern_title[1])) {
            return true;
        }

        return false;
    }

    /**
     * Remove unnecessary characters from param
     * @param $param
     *
     * @return string
     */
    private function cleanParam($param): string
    {
        return strtolower(str_replace(['{', '}'], '', $param));
    }

    /**
     * Remove routes which shouldn't be imported to Seo Manager
     *
     * @return array
     */
    private function cleanRoutes(): array
    {
        $routes = \Route::getRoutes();

        $getRoutes = $routes->get('GET');

        $result = [];
        foreach ($getRoutes as $route) {
            $result[] = ($route->action['domain'] ?? '') . '/' . ltrim($route->uri, '/');
        }

        foreach ($result as $key => $route) {
            foreach ($this->exceptRoutes as $rule) {
                if (strpos($route, $rule) !== false) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getAllModels(): array
    {
        $path = base_path('app') . '/' . config('seo-manager.models_path');

        $models = File::allFiles($path);
        $cleanModelNames = [];

        foreach ($models as $model) {
            $modelPath = $this->cleanFilePath($model->getPathname());
            $reflectionClass = (new \ReflectionClass($modelPath))->getParentClass();
            if ($reflectionClass !== false &&
                in_array($reflectionClass->getName(), [Model::class, "Illuminate\Foundation\Auth\User"], true)
            ) {
                $cleanModel = [
                    'path' => $modelPath,
                    'name' => str_replace('.php', '', $model->getFilename()),
                ];

                $cleanModelNames[] = $cleanModel;
            }
        }

        return $cleanModelNames;
    }

    /**
     * Get Model all Columns
     *
     * @param $model
     *
     * @return array
     */
    public function getColumns($model): array
    {
        $appends = [];

        if (method_exists(new $model, 'getAppends')) {
            $appends = (new $model)->getAppends();
        }

        $table = (new $model)->getTable();
        $columns = $this->getCleanColumns(Schema::getColumnListing($table));

        return array_merge($columns, $appends);
    }

    /**
     * Clean model file path
     *
     * @param $file
     *
     * @return string
     */
    private function cleanFilePath($file): string
    {
        return '\\' . ucfirst(str_replace('/', '\\', substr($file, strpos($file, 'app'), -4)));
    }

    /**
     * Remove unnecessary columns from table columns list
     *
     * @param $columns
     *
     * @return array
     */
    private function getCleanColumns($columns): array
    {
        return array_diff($columns, $this->exceptColumns);
    }

    /**
     * Import Routes to SeoManager database
     *
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    private function importRoutes()
    {
        $routes = $this->cleanRoutes();
        foreach ($routes as $uri) {
            $data = [
                'uri'           => $uri,
                'params'        => $this->getParamsFromURI($uri),
                'keywords'      => [],
                'title_dynamic' => [],
            ];
            if (!SeoManager::where('uri', $uri)->first()) {
                $seoManager = new SeoManager();
                $seoManager->fill($data);
                $seoManager->save();
            }
        }

        $routes = SeoManager::all();

        return $routes;
    }

    /**
     * Get mapped Seo Data from Database for Current Route
     *
     * @param $property
     *
     * @return mixed
     */
    private function getMetaData($property)
    {
        $route = \Route::current();
        $uri = $route->uri();

        if (!empty($route->action['domain'])) {
            $uri = $route->action['domain'] . '/' . ltrim($uri, '/');
        }

        $seoManager = SeoManager::where('uri', $uri)->first();
        if ($seoManager === null) {
            return null;
        }

        $metaData = [];

        if (count($seoManager->keywords) > 0) {
            $metaData['keywords'] = implode(', ', $seoManager->keywords);
        }

        if ($seoManager->description) {
            $metaData['description'] = $seoManager->description;
        }

        if ($seoManager->title) {
            $metaData['title'] = $seoManager->title;
        }

        if ($seoManager->url) {
            $metaData['url'] = $seoManager->url;
        } else {
            $metaData['url'] = url()->full();
        }

        if ($seoManager->author) {
            $metaData['author'] = $seoManager->author;
        }

        if ($seoManager->mapping !== null) {
            $metaData['title_dynamic'] = $this->getDynamicTitle($seoManager->title_dynamic, $seoManager,
                $route->parameters);
        }

        if ($seoManager->og_data) {
            $ogData = $this->getOgData($seoManager, $route->parameters);

            if ($property === 'og_data') {
                $metaData['og_data'] = $ogData;
            } else {
                foreach ($ogData as $key => $og) {
                    $metaData[$key] = $og;
                }
            }
        }

        if ($property !== null && !empty($property)) {
            return $metaData[$property] ?? null;
        }

        return $metaData;
    }

    /**
     * Get dynamic title based on user configs for current route
     *
     * @param $params
     * @param $manager
     * @param $routeParams
     *
     * @return string
     */
    private function getDynamicTitle($params, $manager, $routeParams = null): string
    {
        $dynamicTitle = '';
        if (is_array($params)) {
            foreach ($params as $param) {
                if ($this->isParam($param)) {
                    $param = $this->cleanParam($param);
                    $paramsArray = explode('-', $param);
                    $mapping = $manager->mapping[$paramsArray[0]];
                    $model = $mapping['model']['path'];
                    $findBy = $mapping['find_by'];
                    $selectedColumns = $mapping['selectedColumns'];
                    if (in_array($paramsArray[1], $selectedColumns, true)) {
                        $mappedTitle = new $model;
                        if ($routeParams) {
                            $condition = $routeParams[$paramsArray[0]];

                            if ($condition instanceof Model) {
                                $condition = $condition->$findBy;
                            }

                            $mappedTitle = $mappedTitle->where($findBy, $condition)->first();
                        } else {
                            $mappedTitle = $mappedTitle->first();
                        }

                        if ($mappedTitle) {
                            $dynamicTitle .= optional($mappedTitle)->{$paramsArray[1]} . ' ';
                        }
                    }
                } elseif ($this->isTitle($param)) {
                    $dynamicTitle .= $manager->title . ' ';
                } else {
                    $dynamicTitle .= $param . ' ';
                }
            }
        }

        return $dynamicTitle;
    }

    /**
     * Get Open Graph Dynamic Data
     *
     * @param $seoManager
     * @param $routeParams
     *
     * @return array
     */
    private function getOgData($seoManager, $routeParams): array
    {
        $dataArray = [];
        $value = '';

        foreach ($seoManager->og_data as $key => $og) {
            if (is_array(reset($og['data']))) {
                foreach ($og['data'] as $ogKey => $data) {
                    if ($data['mapped']) {
                        $value = $this->getMappedValue($data['value'], $seoManager, $routeParams);
                    } elseif ($data['value']) {
                        $value = $data['value'];
                    }
                    if ($data['value']) {
                        $dataArray['og:' . $key . ':' . $ogKey] = $value;
                    }
                }
            } else {
                if ($og['data']['mapped']) {
                    $value = $this->getMappedValue($og['data']['value'], $seoManager, $routeParams);
                } elseif ($og['data']['value']) {
                    $value = $og['data']['value'];
                } elseif ($key === 'url') {
                    $value = url()->full();
                }
                if ($og['data']['value'] || $key === 'url') {
                    $dataArray['og:' . $key] = $value;
                }
            }
        }

        return $dataArray;
    }

    /**
     * Get Open Graph Data Values based on Mapped Params
     *
     * @param $value
     * @param $manager
     * @param $routeParams
     *
     * @return mixed
     */
    private function getMappedValue($value, $manager, $routeParams)
    {
        $paramsArray = explode('-', strtolower($value));
        $mapping = $manager->mapping[$paramsArray[0]];
        $model = $mapping['model']['path'];
        $findBy = $mapping['find_by'];
        $selectedColumns = $mapping['selectedColumns'];
        $mapped = null;
        if (in_array($paramsArray[1], $selectedColumns, true)) {
            $mapped = new $model;
            if ($routeParams) {
                $mapped = $mapped->where($findBy, $routeParams[$paramsArray[0]])->first();
            } else {
                $mapped = $mapped->first();
            }
        }

        return optional($mapped)->{$paramsArray[1]};
    }
}
