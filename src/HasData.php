<?php

namespace MOIREI\ModelData;

use Illuminate\Support\Arr as ArrayData;
use MOIREI\ModelData\ModelData;
use MOIREI\ModelData\Data;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasData
{

    /**
     * Get the user's subscriptions.
     *
     * @return MorphOne
     */
    public function modeldata()
    {
        return $this->morphOne(ModelData::Class, 'datable');
    }

    public function setDataAttribute($data)
    {
        $model_data = $this->modeldata()->firstOrCreate([
            'datable_id' => $this->id,
            'datable_type' => get_class($this),
        ]);
        $model_data->update(['data' => $data]);

        return $data;
    }

    public function getDataAttribute(): Data
    {
        /**
         * Ensure the object exists
         */
        $this->modeldata()->firstOrCreate([
            'datable_id' => $this->id,
            'datable_type' => get_class($this),
        ]);

        return Data::create($this, 'data');
    }

    /**
     * Access the data attribute
     * @param string $key
     * @param mix $value
     * @return mix
     */
    public function data($key = null, $value = null){

        $model_data = $this->modeldata()->firstOrCreate([
            'datable_id' => $this->id,
            'datable_type' => get_class($this),
        ]);

        $data = $model_data->data?? [];

        if( ! is_null($value) ){
            ArrayData::set($data, $key, $value);
            $model_data->update(['data' => $data]);
        }

        if( ! is_null($key)  ){
            return ArrayData::has($data, $key)? ArrayData::get($data, $key) : null;
        }

        return $data;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot() {
        parent::boot();

        /**
         * Also save model data whenever model is saved
         */
        static::saved(function($model){
            if( !is_null($model->modeldata) ){
                $model->modeldata->save();
            }
        });
    }

}
