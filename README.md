# Eloquent-Taggable

Easily add the ability to tag your Eloquent models in Laravel.

[![Latest Stable Version](https://poser.pugx.org/cviebrock/eloquent-taggable/v/stable.png)](https://packagist.org/packages/cviebrock/eloquent-taggable)
[![Total Downloads](https://poser.pugx.org/cviebrock/eloquent-taggable/downloads.png)](https://packagist.org/packages/cviebrock/eloquent-taggable)

* [Installation and Requirements](#installation)
* [Updating your Eloquent Models](#eloquent)
* [Usage](#usage)
* [Configuration](#config)
* [Extending Taggable](#extending)
* [Bugs, Suggestions and Contributions](#bugs)
* [Copyright and License](#copyright)


<a name="installation"></a>
## Installation and Requirements


1. Install the `cviebrock/eloquent-taggable` package via composer:

    ```shell
    $ composer require cviebrock/eloquent-taggable
    ```

2. Add the service provider (`app/config/app.php` for Laravel 4, `config/app.php` for Laravel 5):

    ```php
    # Add the service provider to the `providers` array
    'providers' => array(
        ...
        'Cviebrock\EloquentTaggable\ServiceProvider',
    )
    ```

3. Publish the configuration file.

    For Laravel 4:

    ```shell
    php artisan config:publish cviebrock/eloquent-taggable
    ```

    Or for Laravel 5:

    ```shell
    php artisan vendor:publish
    ```

4. Finally, use artisan to run the migration to create the required tables.

    For Laravel 4:

    ```sh
    php artisan migrate --package=cviebrock/eloquent-taggable
    ```

    For Laravel 5:

    ```sh
    php artisan taggable:table
    php artisan migrate
    ```


<a name="eloquent"></a>
## Updating your Eloquent Models

Your models should implement Taggable's interface and use it's trait:

```php
use Cviebrock\EloquentTaggable\Contracts\Taggable;
use Cviebrock\EloquentTaggable\Traits\Taggable as TaggableImpl;

class MyModel extends Eloquent implements Taggable
{
    use TaggableImpl;
}
```

That's it ... your model is now "taggable"!



<a name="usage"></a>
## Using the Class

Tag your models with the `tag()` method:

```php
// Pass in a delimited string:
$model->tag('Apple,Banana,Cherry');

// Or an array:
$model->tag(['Apple', 'Banana', 'Cherry']);
```

The `tag()` method is additive, so you can tag the model again and those tags will be added to the previous ones:

```php
$model->tag('Apple,Banana,Cherry');

$model->tag('Durian');
// $model now has four tags
```

You can remove tags individually with `untag()` or entirely with `detag()`:

```php
$model->tag('Apple,Banana,Cherry');

$model->untag('Banana');
// $model is now just tagged with "Apple" and "Cherry"

$model->detag();
// $model has no tags
```

You can also completely retag a model (a short form for detagging then tagging):

```php
$model->tag('Apple,Banana,Cherry');

$model->retag('Etrog,Fig,Grape');
// $model is now just tagged with "Etrog", "Fig", and "Grape"
```

You can get the array of all tags (technically, an Eloquent Collection):

```php
foreach($model->tags as $tag)
{
    echo $tag->name;
}
```

You can also get the list of tags as a flattened array, or a delimited list:

```php
$model->tag('Apple,Banana,Cherry');

var_dump($model->tagList);

// string 'Apple,Banana,Cherry' (length=19)

var_dump($model->tagArray);

// array (size=3)
//  1 => string 'Apple' (length=5)
//  2 => string 'Banana' (length=6)
//  3 => string 'Cherry' (length=6)
```

Tag names are normalized (see below) so that duplicate tags aren't accidentally created:

```php
$model->tag('Apple');
$model->tag('apple');
$model->tag('APPLE');

var_dump($model->tagList);
// string 'Apple' (length=5)
```

Finally, you can easily find models with tags through some query scopes:

```php
Model::withAllTags('apple,banana,cherry');
// returns models that are tagged with all 3 of those tags

Model::withAnyTags('apple,banana,cherry');
// returns models with any one of those 3 tags

Model::withAnyTags();
// returns models with any tags at all
```



<a name="config"></a>
## Configuration

Configuration is handled through the settings in `/app/config/taggable.php`.  The default values are:

```php

return array(
    'delimiters' => ',;',
    'normalizer' => 'mb_strtolower',
);
```

### delimiters

These are the single-character strings that can delimit the list of tags passed to the `tag()` method.  By default, it's just the comma, but you can change it to another character, or use multiple characters.

For example, if __delimiters__ is set to ";,/", the this will work as expected:

```php
$model->tag('Apple/Banana;Cherry,Durian');
// $model will have four tags
```

When using multiple delimiters, the first one will be used to build strings for the `tagList` attribute.  So, in the above case:

```php
var_dump($model->tagList);

// string 'Apple;Banana;Cherry;Durian' (length=26)
```

### normalizer

Each tag is "normalized" before being stored in the database.  This is so that variations in the spelling or capitalization of tags don't generate duplicate tags.  For example, we don't want three different tags in the following case:

```php
$model->tag('Apple');
$model->tag('APPLE');
$model->tag('apple');
```

Normalization happens by passing each tag name through a normalizer function.  By default, this is PHP's `mb_strtolower()` function, but you can change this to any function or callable that takes a single string value and returns a string value.  Some ideas:

```php

    // default normalization
    'normalizer' => 'mb_strtolower',

    // same result, but using a closure
    'normalizer' => function($string) {
        return mb_strtolower($string);
    },

    // using a class method
    'normalizer' => array('Str','slug'),
```

You can access the normalized values of the tags through `$model->tagListNormalized` and `$model->tagArrayNormalized`, which work identically to `$model->tagList` and `$model->tagArray` (described above) except that they return the normalized values instead.

And you can, of course, access the normalized name directly from a tag:

```php
echo $tag->normalized;
```



<a name="extending"></a>
## Extending Taggable

_Coming soon._



<a name="bugs"></a>
## Bugs, Suggestions and Contributions

Please use Github for bugs, comments, suggestions.

1. Fork the project.
2. Create your bugfix/feature branch and write your (well-commented) code.
3. Create unit tests for your code:
	- Run `composer install --dev` in the root directory to install required testing packages.
	- Add your test methods to `eloquent-taggable/tests/TaggableTest.php`.
	- Run `vendor/bin/phpunit` to the new (and all previous) tests and make sure everything passes.
3. Commit your changes (and your tests) and push to your branch.
4. Create a new pull request against the eloquent-sluggable `master` branch.

> **Note:** You must create your pull request against the `master` branch for the Laravel-5-compatible package.



<a name="copyright"></a>
## Copyright and License

Eloquent-Taggable was written by Colin Viebrock and released under the MIT License. See the LICENSE file for details.

Copyright 2014 Colin Viebrock
