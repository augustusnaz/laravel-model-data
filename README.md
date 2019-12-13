# Add extra (data) attributes to Eloquent models

Have you wished your model could easily add and remove attributes without messing up your migrations? This package provides an easy way to access extra attribute (data) to you eloquent without any changes to your migrations or model codes.



### Features

* Use extra data on eloquent model without defining a new column
* Option to use model's column instead of the package's global `model_data` table
* Handles and return data as `collection`
* `collect` any data and save into any model with the `HasData` trait



## Installation & Setup

The package can be set up in two modes: (1) persist data with the internal model or (2) define a column on your model. A combination of both can also be used with different models.

You can install the package via composer;

```bash
composer require moirei/laravel-model-data
```

First you must install the service provider (skip for Laravel>=5.5);

```php
// config/app.php
'providers' => [
    ...
    MOIREI\ModelData\ModelDataServiceProvider::class,
],
```

Add the `MOIREI\ModelData\HasData` trait to your  model;

```php
use MOIREI\ModelData\HasData;

class YourModel extends Model
{
    use HasData;
    
    ...
}
```

#### Using the package's model

**Optional if using a predefined attribute/column in your models.**

Publish the migration with;

```bash
php artisan vendor:publish --provider="MOIREI\ModelData\ModelDataServiceProvider" --tag="migrations"
```

Run the migrate command to create the necessary table;

```bash
php artisan migrate
```



#### Using a model attribute/column

Define the data column in your migration;

```php
Schema::create('model_table', function (Blueprint $table) {
    $table->modelData('data');
    // OR
    $table->text('data')->nullable();
});
```

Then define a public `model_data` variable in your model;

```php
...
    
/**
  * ModelData: use model's column
  *
  * @var string|false
  */
public $model_data = 'data';

class YourModel extends Model
{
    use HasData;
    
    ...
}
```

Don't forget to cast the attribute to array;

```php
class YourModel extends Model
{
    ...
    
    /**
     * Arrays that should be casted
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];
    
    ...
}
```



## Usage

### Accessing the data

Basic access:

```php
$model->data->name = 'value';
$model->data->name; // Returns 'value'
```

Access as arrays:

```php
$model->data['name'] = 'value';
$model->data['name']; // Returns 'value'
```

Using the`data` method (most recommended);

```php
$model->data('name', 'value');
$model->data('name'); // Returns 'value'

$model->data('moirei.tech', 'awesome');
$model->data('moirei.tech'); // Returns 'awesome'
```

All existing data can be overridden by assigning an array;

```php
// All existing data will be replaced
$model->data = ['name' => 'value'];
$model->data->all(); // Returns ['name' => 'value']
```

With `get` and `set`; 

```php
$model->data = [
   'moirei' => ['tech' => 'awesome'],
   'mg001' => ['resource' => 'white'],
];
$model->data->set('mg001.name', 'Wireless Power Bank');
$model->data->get('mg001.name'); // Returns 'Wireless Power Bank'
```

`get` with default:

```php
$model->data->get('unset_attribute', 'default'); // Returns 'default'
```

**Note**: accessing `$model->data` returns a collection. `$model->data()` on the other hand is a function.



### Persisting data

```php
$model->save();
```

Or

```php
$model->data->save();
```

which only saves data but does not affect your model (if using the package's ModelData). 

Modify and save into a different model with

```php
$model->data
    ->filter()
    ->save($model_2);
```



### Collections

This packages extends and uses Laravel collections in many ways.

Some extended functions are `put`, `forget`, `push`, `offsetSet`. Functions `pinch` and `save` are supplied globally.

This means you can collect and save any data into any model that has the `HasData` trait;

```php
$data = collect([
    'first_name' => 'James',
    'last_name' => 'Franco'
])->save($model);
```

The `pinch` function simply allows you to access a collection's underlying array using the dot notation.



### Retrieving models with Query Builder

```php
$model = YourModel::withData('first_name', 'James')->get();

$model = YourModel::withData([
    'first_name' => 'James',
    'last_name' => 'Franco',
])->get();
```

Use wisely. Since the data column is a not a JSON, the internal query uses `LIKE` notation to match the *stringified* data against the column data.



## Design Notes

- Accessing the data with `$model->data` and `$model->data()` creates a new collection instance on every call.

- If not using the extended collection functions (e.g. `put`, `forget`, `push`) to modify the underlying array, persist your data by using a single instance like

  ```php
  $data = $model->data; // return the created collection instance
  $data->set('name', 'James')
       ->filter()
       ->save();
  ```

- Removing or setting `$model_data` to `false` in your model forces the package to use its internal model to persist data.

  

## Credits

- [Augustus Okoye](https://github.com/augustusnaz)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
