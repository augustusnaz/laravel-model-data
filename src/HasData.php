<?php

namespace MOIREI\ModelData;

use MOIREI\ModelData\ModelData;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

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

    public function scopeWithData(): Builder
    {
        $arguments = func_get_args();

        $use_local = !empty($this->model_data);
        $attribute_name = $use_local? $this->model_data : 'data';

        if (count($arguments) === 1) {
            [$query] = $arguments;
            $data = [];
        }
        if (count($arguments) === 2) {
            [$query, $data] = $arguments;
        }
        if (count($arguments) >= 3) {
            [$query, $name, $value] = $arguments;
            $data = [$name => $value];
        }

        if(is_array($data)){
            foreach ($data as $name => $value){
                $data_text = json_encode([$name => $value]);
                $data_text = preg_replace('/}+/', '', preg_replace('/{+/', '', $data_text)); // replace curly braces

                if($use_local){
                    $query->where($attribute_name, 'like', "%$data_text%");
                }
                else{
                    $query->whereHas('modeldata', function ($query) use($attribute_name, $data_text) {
                        $query->where($attribute_name, 'like', "%$data_text%");
                    });
                }
            }
        }

        return $query;
    }

    public function setDataAttribute($__data__)
    {

        $use_local = !empty($this->model_data);
        $attribute_name = $use_local? $this->model_data : 'data';

        if( $use_local ){
            $this->attributes[ $attribute_name ] = json_encode($__data__);
        }else{
            $model_data = $this->modeldata()->firstOrCreate([
                'datable_id' => $this->getKey(),
                'datable_type' => $this->getMorphClass(),
            ]);
            $model_data->update([$attribute_name => $__data__]);
        }

        return $__data__;
    }

    public function getDataAttribute()
    {

        $use_local = !empty($this->model_data);
        $attribute_name = $use_local? $this->model_data : 'data';

        if( $use_local ){
            $__data__ = json_decode($this->attributes[ $attribute_name ], true);
        }else{
            $this->modeldata()->firstOrCreate([
                'datable_id' => $this->getKey(),
                'datable_type' => $this->getMorphClass(),
            ]);

            if($this->modeldata){
                $__data__ = $this->modeldata->{$attribute_name}?? [];
            }else{
                $__data__ = [];
            }
        }

        $data = Collection::create( $this, $__data__, $use_local, $attribute_name );

        return $data;
    }

    /**
     * Access the data attribute
     * @param string $key
     * @param mix $value
     * @return mix
     */
    public function data($key = null, $value = null){

        $use_local = !empty($this->model_data);
        $attribute_name = $use_local? $this->model_data : 'data';

        if( $use_local ){
            $__data__ = json_decode($this->attributes[ $attribute_name ], true);
        }else{
            $model_data = $this->modeldata()->firstOrCreate([
                'datable_id' => $this->getKey(),
                'datable_type' => $this->getMorphClass(),
            ]);
            $__data__ = $model_data->data?? [];
        }

        if( !is_null($value) ){
            Arr::set($__data__, $key, $value);
            if( $use_local ){
                $this->attributes[ $attribute_name ] = json_encode($__data__);
            }else{
                $this->modeldata->update([$attribute_name => $__data__]);
            }
        }

        $data = Collection::create( $this, $__data__, $use_local, $attribute_name );

        if( !is_null($key) ){
            return $data->pinch($key);
        }

        return $data->all();
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
