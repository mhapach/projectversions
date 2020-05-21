<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 14.05.2020
 * Time: 15:48
 */

namespace mhapach\ProjectVersions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use mhapach\ProjectVersions\Console\ProjectVersionsCommit;
use mhapach\ProjectVersions\Libs\Vcs\BaseVcs;

class ProjectVersionsServiceProvider extends ServiceProvider
{

    public function boot(){

        require_once __DIR__.'/../src/Http/routes.php';

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'projectversions');

        if ($this->app->runningInConsole())
            $this->bootForConsole();

        $this->app->singleton('version', function ($app) {
            $version = BaseVcs::version() ?? 0;
            View::share('version', $version);
            return $version;
        });

    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/project_versions.php', 'projectversions');

        // Register the service the package provides.
//        $this->app->singleton('project_versions', function ($app) {
//            return new SwaggerModelGenerator;
//        });
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['projectversions'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/project_versions.php' => config_path('project_versions.php'),
        ], 'projectversions.config');

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/mhapach'),
        ], 'projectversions.views');

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/mhapach'),
        ], 'swaggermodelgenerator.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/mhapach'),
        ], 'swaggermodelgenerator.views');*/

        // Registering package commands.
         $this->commands([
             ProjectVersionsCommit::class
         ]);
    }
}
