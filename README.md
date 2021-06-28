# laravel-model-data

This package allows you to access attribute fields in your model or else use a **data** field without any changes to your migrations.

### Features

- Use extra data on eloquent model without defining a new column
- Easily interact with multiple custom fields on your model as data fields
- `collect` any data and save into any model with the `HasData` trait



## Installation & Setup

You can install the package via composer;

```bash
composer require moirei/laravel-model-data
```

Add the `MOIREI\ModelData\HasData` trait to your model;

```php
use MOIREI\ModelData\HasData;

class YourModel extends Model
{
    use HasData;

    ...
}
```

#### Using the package's model

*Optional if using a predefined attribute/column in your models.*

Publish the migration with;

```bash
php artisan vendor:publish --tag="model-data-migrations"
```

Run the migrate command to create the necessary table;

```bash
php artisan migrate
```



### Mode 1: Persist data outside your model

This is the default mode if the `$model_data` property is falsy.

### Mode 2: Persist data in your model

Define the data column (s) in your migration;

```php
Schema::create('products', function (Blueprint $table) {
    $table->modelData('data');
    // OR
    $table->json('data')->nullable();
    // OR
    $table->text('data')->nullable();
});
```

Then define a public `model_data` variable in your model. This is the name of the storage column.

```php
...

/**
  * ModelData: use model's column
  *
  * @var string|array|false
  */
protected $model_data = 'data';

class YourModel extends Model
{
    use HasData;

    ...
}
```



## Usage

### Accessing the data

The access below uses *`data`* if in *mode 1* or the of `$model_data` is `data`;

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

Calling as function. 

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
// or
$mdeol->data->set([
   'moirei' => ['tech' => 'awesome'],
   'mg001' => ['resource' => 'white'],
]);

$model->data->set('mg001.name', 'Wireless Power Bank');
$model->data->get('mg001.name'); // Returns 'Wireless Power Bank'
```

`get` with default:

```php
$model->data->get('undefined-attribute', 'default'); // Returns 'default'
```



### Multiple/custom data fields (mode 2)

The above example uses `data` field which is the default for external mode. To allow multiple access with custom names,

```php
...

/**
  * ModelData: use model's column
  *
  * @var string|array|false
  */
// protected $model_data = 'settings';
protected $model_data = [
    'settings', 'meta', 
];

...
```

Access with

```php
// set values
$model->settings->set([]);
$model->meta->set([]);

// get values
dump($model->settings());
dump($model->meta());
```



### Persisting data

```php
$model->save();
// or
$model->data->save();
```

Modify and save into a different model with

```php
$model->data->filter()->save($model_2, $key = 'data');
```



### Collections

This packages supplies Collections functions `pinch` and `save`  globally.

This means you can collect and save any data into any model that has the `HasData` trait;

```php
$data = collect([
    'first_name' => 'James',
    'last_name' => 'Franco'
])->save($model, $key = 'data');
```

The `pinch` function simply allows you to access a collection's underlying array using the dot notation.

The `key` option defaults to `data`. 



## Credits

- [Augustus Okoye](https://github.com/augustusnaz)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
