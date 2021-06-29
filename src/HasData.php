<?php

namespace MOIREI\ModelData;

use MOIREI\ModelData\ModelData;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait HasData
{
    protected $modelDataCollection = [];

    /**
     * Get the user's subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function modeldata(): MorphOne
    {
        return $this->morphOne(ModelData::class, 'datable');
    }

    public function __set($key, $value)
    {
        if (!$this->isModelData($key)) {
            return parent::__set($key, $value);
        }

        if (!Arr::has($this->modelDataCollection, $key)) {
            Arr::set($this->modelDataCollection, $key, Collection::create($this));
        }

        $this->modelDataCollection[$key]->set($value);

        return $value;
    }

    public function __get($key)
    {
        if (!$this->isModelData($key)) {
            return parent::__get($key);
        }

        if (!Arr::has($this->modelDataCollection, $key)) {
            Arr::set($this->modelDataCollection, $key, Collection::create($this, $this->getModelData($key)));
        }

        return $this->modelDataCollection[$key];
    }

    private function getModelData(string $key): array
    {
        if ($this->isLocalModelData($key)) {
            $data = $this->attributes[$key];
        } else {
            if ($this->modeldata()->exists()) {
                $data = $this->modeldata->data;
            } else {
                $data = [];
            }
        }

        if (is_array($data)) {
            return $data;
        }

        return json_decode($data, true) ?? [];
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (!$this->isLocalModelData($method)) {
            return parent::__call($method, $parameters);
        }

        $data = $this->$method;
        $key = data_get($parameters, 0);
        $value = data_get($parameters, 1);

        if ($value) {
            $data->set($key, $value);
        }

        if ($key) {
            return $data->pinch($key);
        }

        return $data->all();
    }

    /**
     * Check whether the model stores data locally
     *
     * @return bool
     */
    public function usesLocalData(): bool
    {
        if ($this->model_data === false) {
            return false;
        }
        if (isset($this->model_data)) {
            return true;
        }
        if (Schema::hasColumn($this->getTable(), 'data')) {
            return true;
        }

        return false;
    }

    /**
     * Get the storage attribute name
     *
     * @return array
     */
    public function getDataAttributeNames(): array
    {
        if (!isset($this->model_data)) return ['data'];
        return is_array($this->model_data) ? $this->model_data : [$this->model_data];
    }

    /**
     * Check if key is local data
     */
    private function isLocalModelData(string $key): bool
    {
        if (($key === 'data') && !Schema::hasColumn($this->getTable(), 'data')) {
            return false;
        }

        return $this->isModelData($key);
    }

    /**
     * Check if key is local data
     */
    private function isModelData(string $key): bool
    {
        if (empty($this->model_data) && ($key === 'data')) {
            return true;
        }
        if (isset($this->model_data)) {
            if (is_string($this->model_data) && $this->model_data === $key) {
                return true;
            }
            if (is_array($this->model_data) && in_array($key, $this->model_data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Save modeldata external
     */
    public function saveModelDataExternal()
    {
        foreach ($this->modelDataCollection as $name => $modelDataCollection) {
            if (!$this->isLocalModelData($name)) {
                $data = $modelDataCollection->all();
                if ($this->modeldata()->exists()) {
                    $this->modeldata->update(['data' => $data]);
                } else {
                    $this->modeldata()->create(['data' => $data]);
                }
            }
        }
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $dataAttributes = $this->getDataAttributeNames();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            if ($this->isFillable($key) && in_array($key, $dataAttributes)) {
                $this->$key = $value;
                unset($attributes[$key]);
            }
        }

        return parent::fill($attributes);
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function bootHasData()
    {
        static::creating(function (Model $model) {
            foreach ($model->modelDataCollection as $name => $modelDataCollection) {
                if ($model->isLocalModelData($name)) {
                    $model->setAttribute($name, json_encode($modelDataCollection->all()));
                }
            }
        });
        static::saving(function (Model $model) {
            foreach ($model->modelDataCollection as $name => $modelDataCollection) {
                if ($model->isLocalModelData($name)) {
                    $model->setAttribute($name, json_encode($modelDataCollection->all()));
                }
            }
        });

        static::created(function (Model $model) {
            $model->saveModelDataExternal();
        });

        static::saved(function (Model $model) {
            $model->saveModelDataExternal();
        });
    }
}
