<?php

namespace MOIREI\ModelData;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class ModelDataServiceProvider extends ServiceProvider
{
    public function register()
    {
        Blueprint::macro('modelData', function (string $column_name = 'data') {
            return $this->text($column_name)->nullable();
        });

        Collection::macro('pinch', function ($key, $default = null) {
            $data = $this->all();
            return Arr::has($data, $key)? Arr::get($data, $key) : $default;
        });

        Collection::macro('save', function ($model = null) {
            if($model instanceof Model){

                $use_local = !empty($model->model_data);
                $attribute_name = $use_local? $model->model_data : 'data';

                if($use_local){
                    $model->update([$attribute_name => $this->all()]);
                }else{
                    $model->modeldata->update([$attribute_name => $this->all()]);
                }
            }

            return $this;
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

            if (! class_exists('CreateModelsDataTable')) {
                $timestamp = date('Y_m_d_His', time());

                $this->publishes([
                    __DIR__.'/../migrations/create_models_data_table.php.stub' => $this->app->databasePath().'/migrations/'.$timestamp.'_create_models_data_table.php',
                ], 'migrations');
            }

        }
    }
}
