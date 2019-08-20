<?php

namespace MOIREI\ModelData;

use Countable;
use ArrayAccess;
use Illuminate\Support\Arr as ArrayData;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

class Data implements ArrayAccess, Arrayable, Countable
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Name of the table column
     *
     * @var string
     */
    protected $attributeName = 'data';

    /**
     * @var array
     */
    protected $modelData = [];

    /**
     * Create a Data instance
     *
     * @param Model $model
     * @param string $attributeName
     * @return Data
     */
    public static function create(Model $model, string $attributeName): self
    {
        return new static($model, $attributeName);
    }

    public function __construct(Model $model, string $attributeName)
    {
        $this->model = $model;

        $this->attributeName = $attributeName;

        $this->modelData = $this->getRawModelData();
    }

    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    public function set($attribute, $value = null)
    {
        if (is_iterable($attribute)) {
            foreach ($attribute as $attribute => $value) {
                $this->set($attribute, $value);
            }

            return;
        }

        data_set($this->modelData, $attribute, $value);

        $this->model->modeldata->{$this->attributeName} = $this->modelData;
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function get(string $name, $default = null)
    {
        return data_get($this->modelData, $name, $default);
    }

    public function has(string $name): bool
    {
        return ArrayData::has($this->modelData, $name);
    }

    public function forget(string $name): self
    {
        $this->model->modeldata->{$this->attributeName} = ArrayData::except($this->modelData, $name);

        return $this;
    }

    public function all(): array
    {
        return $this->getRawModelData();
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        $this->forget($offset);
    }

    public function count()
    {
        return count($this->modelData);
    }

    public function toArray(): array
    {
        return $this->all();
    }

    protected function getRawModelData()
    {
        if(!$this->model->modeldata) return [];

        return $this->model->modeldata->{$this->attributeName}?? [];
    }

    public function save()
    {
        $this->model->modeldata->save();
    }

}
