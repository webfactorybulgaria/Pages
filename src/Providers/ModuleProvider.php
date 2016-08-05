<?php

namespace TypiCMS\Modules\Pages\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use TypiCMS\Modules\Core\Custom\Observers\FileObserver;
use TypiCMS\Modules\Core\Custom\Services\Cache\LaravelCache;
use TypiCMS\Modules\Pages\Custom\Events\ResetChildren;
use TypiCMS\Modules\Pages\Custom\Models\Page;
use TypiCMS\Modules\Pages\Custom\Models\PageTranslation;
use TypiCMS\Modules\Pages\Custom\Observers\AddToMenuObserver;
use TypiCMS\Modules\Pages\Custom\Observers\HomePageObserver;
use TypiCMS\Modules\Pages\Custom\Observers\SortObserver;
use TypiCMS\Modules\Pages\Custom\Observers\UriObserver;
use TypiCMS\Modules\Pages\Custom\Repositories\CacheDecorator;
use TypiCMS\Modules\Pages\Custom\Repositories\EloquentPage;

class ModuleProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'typicms.pages'
        );

        $modules = $this->app['config']['typicms']['modules'];
        $this->app['config']->set('typicms.modules', array_merge(['pages' => []], $modules));

        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'pages');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'pages');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/pages'),
        ], 'views');
        $this->publishes([
            __DIR__.'/../database' => base_path('database'),
        ], 'migrations');

        AliasLoader::getInstance()->alias(
            'Pages',
            'TypiCMS\Modules\Pages\Custom\Facades\Facade'
        );

        // Observers
        Page::observe(new FileObserver());
        Page::observe(new HomePageObserver());
        Page::observe(new SortObserver());
        Page::observe(new AddToMenuObserver());
        PageTranslation::observe(new UriObserver());
    }

    public function register()
    {
        $app = $this->app;

        /*
         * Register route service provider
         */
        $app->register('TypiCMS\Modules\Pages\Custom\Providers\RouteServiceProvider');

        /*
         * Sidebar view composer
         */
        $app->view->composer('core::admin._sidebar', 'TypiCMS\Modules\Pages\Custom\Composers\SidebarViewComposer');

        /*
         * Events
         */
        $app->events->subscribe(new ResetChildren());

        $app->bind('TypiCMS\Modules\Pages\Custom\Repositories\PageInterface', function (Application $app) {
            $repository = new EloquentPage(new Page());
            if (!config('typicms.cache')) {
                return $repository;
            }
            $laravelCache = new LaravelCache($app['cache'], ['pages', 'galleries'], 10);

            return new CacheDecorator($repository, $laravelCache);
        });
    }
}
