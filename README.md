# Add extra (data) attributes to Eloquent models

Have you wished your model could easily add and remove attributes without messing up your migrations? This package provides an easy way to access extra attribute (data) to you eloquent without any changes to your migrations or model codes.



## Installation

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
}
```

Next publish the migration with;

```
php artisan vendor:publish --provider="MOIREI\ModelData\ModelDataServiceProvider" --tag="migrations"
```

Run the migrate command to create the necessary table;

```
php artisan migrate
```

## Usage

### Accessing the data

This is the easiest way to access data attributes:

```php
$model->data->name = 'value';
$model->data->name; // Returns 'value'
```

Array approach:

```php
$model->data['name'] = 'value';
$model->data['name']; // Returns 'value'
```

Alternatively you can use `data` method;

```php
$model->data('name', 'value');
$model->data('name'); // Returns 'value'

$model->data('moirei.tech', 'awesome');
$model->data('moirei.tech'); // Returns 'awesome'
```

Replace all existing data by assigning an array;

```php
// All existing data will be replaced
$model->data = ['name' => 'value'];
$model->data->all(); // Returns ['name' => 'value']
```

You can use `get` and `set`. The methods also support the dot notation.

```php
$model->data = [
   'moirei' => ['tech' => 'awesome'],
   'samsung' => ['resource' => 'white'],
];
$model->data->set('moirei.tech', 'awesome');
$model->data->get('moirei.tech'); // Returns 'awesome'
```

You can also pass a default value to the `get` method.

```php
$model->data->get('unset_attribute', 'default'); // Returns 'default'
```

### Persisting data

```php
$model->save();
```

Or

```php
$model->data->save();
```

which only saves data but does not affect your model. Access via the `data` method automatically persists data;

```php
$model->data('name', 'value');
```

also calls the `save` method.



## Design Notes

- This package is not recommended for data-heavy models and application query





## Credits

- [Augustus Okoye](https://github.com/augustusnaz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
