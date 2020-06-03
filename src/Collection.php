<?php

namespace MOIREI\ModelData;

use Illuminate\Database\Eloquent\Model;

class Collection extends \Illuminate\Support\Collection{

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * Use model's local attribute
     *
     * @var boolean
     */
    private $use_local;

    /**
     * @var string
     */
    private $attribute_name;

    /**
     * Create a new collection.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  mixed  $items
     * @param  boolean $use_local
     * @param  string $attribute_name
     */
    public function __construct(Model $model, $items = [], $use_local = false, $attribute_name = 'data')
    {
        $this->model = $model;
        $this->use_local = $use_local;
        $this->attribute_name = $attribute_name;

        $item = empty($item)? $this->getModelData() : $items;

        parent::__construct($items);
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  mixed $items
     * @param  boolean $use_local
     * @param  string $attribute_name
     * @return static
     */
    public static function create(Model $model, $items = [], $use_local = false, $attribute_name = 'data')
    {
        return new static($model, $items, $use_local, $attribute_name);
    }

    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this, $name], $arguments);
        $this->override($this->items);

        return $result;
    }

    public function __set($key, $value){
        $this->set($key, $value);

        return $value;
    }

    public function __get($key){
        return $this->get($key);
    }

    public function set($key, $value = null)
    {
        if (is_iterable($key)) {
            return $this->override($this->merge($key));
        }

        return $this->override(data_set($this->items, $key, $value));
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function put($key, $value)
    {
        parent::put($key, $value);

        return $this->override($this->items);
    }

    public function pinch($key, $default = null)
    {
        return data_get($this->items, $key, $default);
    }

    public function forget($keys)
    {
        parent::forget($keys);

        return $this->override($this->items);
    }

    public function push(...$value)
    {
        parent::push($value);

        return $this->override($this->items);
    }

    private function getModelData(): array
    {
        if( $this->use_local ){
            $data = $this->model->getOriginal( $this->attribute_name );
        }else{
            $data = $this->model->modeldata->getOriginal( $this->attribute_name );
		}

		if(is_array($data)){
			return $data;
		}

        return json_decode($data, true)?? [];
    }

    private function override(iterable $items)
    {
        parent::__construct($items);

        if( $this->use_local ){
            $this->model->{$this->attribute_name} = $this->items;
        }else{
            $this->model->modeldata->update([$this->attribute_name => $this->items]);
        }

        return $this;
    }

    public function save($model = null) {

        if( !($model instanceof Model) ){
            $model = $this->model;
        }

        if($this->use_local){
            $model->save();
        }else{
            $model->modeldata->save();
        }

        return $this;
    }
}