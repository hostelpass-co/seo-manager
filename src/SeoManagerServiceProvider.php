<?php
declare(strict_types = 1);

namespace Krasov\SeoManager;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Krasov\SeoManager\Commands\GenerateSeoManagerData;

/**
 * Class SeoManagerServiceProvider
 *
 * @package Krasov\SeoManager
 */
class SeoManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/seo-manager.php');
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        $this->loadViewsFrom(__DIR__ . '/views', 'seo-manager');

        $this->publishes([
            __DIR__ . '/config/seo-manager.php' => config_path('seo-manager.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/assets' =>  public_path('vendor/dkrasov'),
        ], 'assets');

        $this->commands([
            GenerateSeoManagerData::class
        ]);

        $this->registerHelpers();
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', \Krasov\SeoManager\Middleware\ClearViewCache::class);

        if (config('seo-manager.shared_meta_data')) {
            $router->pushMiddlewareToGroup('web', \Krasov\SeoManager\Middleware\SeoManager::class);
        }

        // Blade Directives
        $this->registerBladeDirectives();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/seo-manager.php', 'seo-manager'
        );

        $this->app->bind('seomanager', static function (): SeoManager {
            return new SeoManager();
        });

        $this->app->alias('seomanager', SeoManager::class);
    }

    /**
     * Register helpers file
     *
     * @return void
     */
    public function registerHelpers(): void
    {
        // Load helpers
        if (file_exists($file = __DIR__ . '/helpers/helpers.php')) {
            require $file;
        }
    }

    /**
     * Register Blade Directives
     *
     * @return void
     */
    public function registerBladeDirectives(): void
    {
        Blade::directive('meta', static function ($expression): string {
            $meta = '';
            $expression = trim($expression, '\"\'');
            $metaData = metaData($expression);

            if (is_array($metaData)) {
                foreach ($metaData as $key => $og) {
                    $meta .= "<meta property='{$key}' content='{$og}'/>";
                }
            } else {
                $meta .= "<meta property='{$expression}' content='{$metaData}'/>";
            }

            return $meta;
        });

        Blade::directive('keywords', static function (): string {
            return "<meta property='keywords' content='" . metaKeywords() . "'/>";
        });

        Blade::directive('url', static function (): string {
            return "<meta property='url' content='" . metaUrl() . "'/>";
        });

        Blade::directive('author', static function (): string {
            return "<meta property='author' content='" . metaAuthor() . "'/>";
        });

        Blade::directive('description', static function (): string {
            return "<meta property='description' content='" . metaDescription() . "'/>";
        });

        Blade::directive('title', static function (): string {
            return "<meta property='title' content='" . metaTitle() . "'/>";
        });

        Blade::directive('openGraph', static function ($expression): string {
            $expression = trim($expression, '\"\'');
            $meta = '';
            $metaOpenGraph = metaOpenGraph($expression);
            if (is_array($metaOpenGraph)) {
                foreach ($metaOpenGraph as $key => $og) {
                    $meta .= "<meta property='{$key}' content='{$og}'/>";
                }
            } else {
                $meta .= "<meta property='{$expression}' content='{$metaOpenGraph}'/>";
            }
            return $meta;
        });

        Blade::directive('titleDynamic', static function () {
            return metaTitleDynamic();
        });
    }
}
