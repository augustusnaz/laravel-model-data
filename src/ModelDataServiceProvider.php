<?php

namespace MOIREI\ModelData;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class ModelDataServiceProvider extends ServiceProvider
{
    public function register()
    {
        Blueprint::macro('schemalessAttributes', function (string $columnName = 'schemaless_attributes') {
            return $this->json($columnName)->nullable();
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->definePublishing();
        }
    }

    /**
     * Define the publishable migrations and resources.
     *
     * @return void
     */
    protected function definePublishing()
    {
        if (! class_exists('CreateModelsDataTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../migrations/create_models_data_table.php.stub' => $this->app->databasePath().'/migrations/'.$timestamp.'_create_models_data_table.php',
            ], 'migrations');
        }
    }
}
