<?php

namespace Brucelwayne\Subscribe;

use Brucelwayne\Subscribe\Models\EmailSubscriberModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SubscribeServiceProvider extends ServiceProvider
{
    protected string $module_name = 'brucelwayne-subscribe';

    public function register()
    {

    }

    public function boot()
    {

        Relation::enforceMorphMap([
            'email_subscriber' => EmailSubscriberModel::class,
        ]);

        $this->bootFacades();
        $this->bootConfigs();
        $this->bootRoutes();
        $this->bootMigrations();
        $this->bootComponentNamespace();
        $this->loadBladeViews();
    }

    protected function bootFacades(): void
    {

    }

    protected function loadBladeViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->module_name);
    }

    protected function bootComponentNamespace(): void
    {
        Blade::componentNamespace('Brucelwayne\\Subscribe\\View\\Components', $this->module_name);
    }

    protected function bootConfigs(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/brucelwayne-subscribe.php', $this->module_name
        );
    }

    protected function bootRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
    }

    protected function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}