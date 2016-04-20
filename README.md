# Eloquent-Taggable

Easily add the ability to tag your Eloquent models in Laravel 5.

[![Latest Version](https://img.shields.io/packagist/v/cviebrock/eloquent-taggable.svg?style=flat-square)](https://github.com/cviebrock/eloquent-taggable/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/cviebrock/eloquent-taggable.svg?style=flat-square)](https://packagist.org/packages/cviebrock/eloquent-taggable)
[![Build Status](https://img.shields.io/travis/cviebrock/eloquent-taggable/master.svg?style=flat-square)](https://travis-ci.org/cviebrock/eloquent-taggable)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/9e1bb86e-2659-4123-9b6f-89370ef1483d.svg?style=flat-square)](https://insight.sensiolabs.com/projects/9e1bb86e-2659-4123-9b6f-89370ef1483d)
[![Quality Score](https://img.shields.io/scrutinizer/g/cviebrock/eloquent-taggable.svg?style=flat-square)](https://scrutinizer-ci.com/g/cviebrock/eloquent-taggable)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)


* [Installation](#installation)
* [Updating your Eloquent Models](#updating-your-eloquent-models)
* [Usage](#usage)
* [The TagService Class](#the-tagservice-class)
* [Configuration](#configuration)
* [Bugs, Suggestions and Contributions](#bugs-suggestions-and-contributions)
* [Copyright and License](#copyright-and-license)


> *NOTE:* If you are using Laravel 4, then please use the `1.*` branch and releases.

---

## Installation


1. Install the `cviebrock/eloquent-taggable` package via composer:

    ```shell
    $ composer require cviebrock/eloquent-taggable
    ```
    
2. Add the service provider to `config/app.php`:

    ```php
    # Add the service provider to the `providers` array
    'providers' => array(
        ...
        \Cviebrock\EloquentTaggable\ServiceProvider::class,
    )
    ```

3. Publish the configuration file and migrations

    ```shell
    php artisan vendor:publish --provider="Cviebrock\EloquentTaggable\ServiceProvider"
    ```

4. Finally, use artisan to run the migration to create the required tables.

    ```sh
    composer dump-autoload
    php artisan migrate
    ```


## Updating your Eloquent Models

Your models should use the Taggable trait:

```php
use Cviebrock\EloquentTaggable\Taggable;

class MyModel extends Eloquent
{
    use Taggable;
}
```

That's it ... your model is now "taggable"!


## Usage

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

You can easily find models with tags through some query scopes:

```php
Model::withAllTags('apple,banana,cherry');
// returns models that are tagged with all 3 of those tags

Model::withAnyTags('apple,banana,cherry');
// returns models with any one of those 3 tags

Model::withAnyTags();
// returns models with any tags at all
```

Finally, you can easily find all the tags used across all instances of a model:

```php
Model::allTags();
// returns an array of all the tags used by any Model instances
```


## The TagService Class

There a few other things you can do using the `TagService` class directly,
such as getting an `Illuminate\Database\Eloquent\Collection` of all the tag
models for a given class:

```php
$service = app(\Cviebrock\EloquentTaggable\Services\TagService::class);
$tags = $service->getAllTags(\App\Model::class);
```

All the functionality you get from using the model methods is driven
(in part) by methods in the service class, and most of those methods are
public and so you can access them directly if you need to.

As always, take a look at the code for full documention of those methods.


## Configuration

Configuration is handled through the settings in `/app/config/taggable.php`.  The default values are:

```php

return array(
    'delimiters' => ',;',
    'glue' => ',',
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

### glue

When building a string for the `tagList` attribute, this is the "glue" that is used to join tags.  With the default values, in the above case:

```php
var_dump($model->tagList);

// string 'Apple,Banana,Cherry,Durian' (length=26)
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


## Bugs, Suggestions and Contributions

Thanks to [everyone](https://github.com/cviebrock/eloquent-taggable/graphs/contributors)
who has contributed to this project!

Please use [Github](https://github.com/cviebrock/eloquent-taggable) for reporting bugs, 
and making comments or suggestions.
 
See [CONTRIBUTING.md](CONTRIBUTING.md) for how to contribute changes.


## Copyright and License

[eloquent-taggable](https://github.com/cviebrock/eloquent-taggable)
was written by [Colin Viebrock](http://viebrock.ca) and is released under the 
[MIT License](LICENSE.md).

Copyright (c) 2013 Colin Viebrock
