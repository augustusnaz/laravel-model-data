<?php

namespace MOIREI\ModelData;

use Illuminate\Database\Eloquent\Model;

class Collection extends \Illuminate\Support\Collection
{

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * Create a new collection.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  mixed  $items
     */
    public function __construct(Model $model, $items = [])
    {
        $this->model = $model;
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  mixed $items
     * @return static
     */
    public static function create(Model $model, $items = [])
    {
        return new static($model, $items);
    }

    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this, $name], $arguments);
        $this->override($this->items);

        return $result;
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);

        return $value;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function set($key, $value = null)
    {
        if (is_null($value)) {
            return $this->override($key);
        }

        if (is_iterable($key)) {
            return $this->override($this->merge($key));
        }

        return $this->override(data_set($this->items, $key, $value));
    }

    public function pinch($key, $default = null)
    {
        return data_get($this->items, $key, $default);
    }

    private function override($data)
    {
        $this->items = $this->getArrayableItems($data);

        return $this;
    }

    /**
     * Save the collection data into its original model or a new model
     *
     * @param Model|null $model
     * @param string $key
     * @return self
     */
    public function save(Model|null $model = null, string $key = 'data')
    {
        if ($model) {
            $model->$key = $this->all();
        } else {
            $model = $this->model;
        }

        $model->save();

        return $this;
    }
}
