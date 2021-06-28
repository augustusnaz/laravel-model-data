<?php

namespace MOIREI\ModelData;

use Illuminate\Database\Eloquent\Model;

class ModelData extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data',
    ];

    /**
     * Cast data to native array.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * The connection table
     *
     * @var string
     */
    protected $table = 'models_data';

    /**
     * Get all of the models that own push_subscription.
     */
    public function datable()
    {
        return $this->morphTo();
    }
}
