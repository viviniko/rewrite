<?php

namespace Viviniko\Rewrite;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Viviniko\Rewrite\Console\Commands\RewriteTableCommand;
use Viviniko\Rewrite\Facades\Rewrite;

class RewriteServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/../config/rewrite.php' => config_path('rewrite.php'),
        ]);

        // Register commands
        $this->commands('command.rewrite.table');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/rewrite.php', 'rewrite');

        $this->registerRepositories();

        $this->registerServices();

        $this->registerCommands();

        Route::macro('rewrite', function ($entityType, $targetRoute) {
            Rewrite::rewrite($entityType, $targetRoute);
        });

        Paginator::currentPathResolver(function () {
            return (Rewrite::request() ?? $this->app['request'])->url();
        });
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->singleton('command.rewrite.table', function ($app) {
            return new RewriteTableCommand($app['files'], $app['composer']);
        });
    }

    protected function registerRepositories()
    {
        $this->app->singleton(
            \Viviniko\Rewrite\Repositories\EntityRepository::class,
            \Viviniko\Rewrite\Repositories\EloquentEntity::class
        );
    }

    /**
     * Register the rewrite service provider.
     *
     * @return void
     */
    protected function registerServices()
    {
        $this->app->singleton('rewrite', \Viviniko\Rewrite\Services\RewriteServiceImpl::class);
        $this->app->alias('rewrite', \Viviniko\Rewrite\Services\RewriteService::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
        ];
    }
}