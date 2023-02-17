# Eloquent-Taggable

Easily add the ability to tag your Eloquent models in Laravel.

> **NOTE**: These instructions are for the latest version of Laravel.  
> If you are using an older version, please install a version of the package
> that [correlates to your Laravel version](#installation).

[![Build Status](https://github.com/cviebrock/eloquent-taggable/workflows/tests/badge.svg?branch=master)](https://github.com/cviebrock/eloquent-taggable/actions)
[![Total Downloads](https://poser.pugx.org/cviebrock/eloquent-taggable/downloads?format=flat)](https://packagist.org/packages/cviebrock/eloquent-taggable)
[![Latest Stable Version](https://poser.pugx.org/cviebrock/eloquent-taggable/v/stable?format=flat)](https://packagist.org/packages/cviebrock/eloquent-taggable)
[![Latest Unstable Version](https://poser.pugx.org/cviebrock/eloquent-taggable/v/unstable?format=flat)](https://packagist.org/packages/cviebrock/eloquent-taggable)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9e1bb86e-2659-4123-9b6f-89370ef1483d/mini.png)](https://insight.sensiolabs.com/projects/9e1bb86e-2659-4123-9b6f-89370ef1483d)
[![License](https://img.shields.io/packagist/l/cviebrock/eloquent-taggable)](LICENSE.md)


* [Installation](#installation)
* [Updating your Eloquent Models](#updating-your-eloquent-models)
* [Usage](#usage)
* [Query Scopes](#query-scopes)
* [The Tag Model](#the-tag-model)
* [The TagService Class](#the-tagservice-class)
* [Configuration](#configuration)
* [Bugs, Suggestions, Contributions and Support](#bugs-suggestions-contributions-and-support)
* [Copyright and License](#copyright-and-license)


---

## Installation

Depending on your version of Laravel, you should install a different
version of the package.  **NOTE**: As of version 6.0, the package's 
version should match the Laravel version.

| Laravel Version | Package Version |
|:---------------:|:---------------:|
|     ^10.0       |     ^10.0       |
|       9.0       |      ^9.0       |
|       8.0       |      ^8.0       |
|       7.0       |      ^7.0       |
|       6.0       |      ^6.0       |
|       5.8       |      3.5.*      |
|       5.7       |      3.4.*      |
|       5.6       |      3.3.*      |
|       5.5       |      3.2.*      |
|       5.4       |     3.1.*†      |

† Version 3.1 of the package requires PHP 7.0 or later, even though Laravel 5.4 doesn't.

Older versions of Laravel can use older versions of the package, although they
are no longer supported or maintained.  See [CHANGELOG.md](CHANGELOG.md) and
[UPGRADING.md](UPGRADING.md) for specifics, and be sure that you are reading
the correct README.md for your version (GitHub displays the version in
the _master_ branch by default, which might not be what you want).


1. Install the `cviebrock/eloquent-taggable` package via composer:

    ```sh
    $ composer require cviebrock/eloquent-taggable
    ```

    The package will automatically register its service provider.

2. Publish the configuration file:

    ```sh
    php artisan vendor:publish --provider="Cviebrock\EloquentTaggable\ServiceProvider" --tag "config"
    ```

3. Publish the migrations:

    ```sh
    php artisan vendor:publish --provider="Cviebrock\EloquentTaggable\ServiceProvider" --tag "migrations"
    ```

If you modify the migrations, keep in mind that you can add more fields,
but shouldn't remove any existing ones.

Also note that if you want to change the table names used for the package, this
should be done in the configuration file (under the `tables` key).

4. Finally, use artisan to run the migration to create the required tables:

    ```sh
    composer dump-autoload
    php artisan migrate
    ```

   (Note that the migration file isn't published to your application, but will run anyway.)


## Updating your Eloquent Models

Your models should use the Taggable trait:

```php
use Cviebrock\EloquentTaggable\Taggable;

class MyModel extends Eloquent
{
    use Taggable;
}
```

> **NOTE**: Make sure your model doesn't have an attribute and/or column in its database table called `tags`; the trait will add that attribute for you.

That's it ... your model is now "taggable"!


## Usage

### Adding and Removing Tags from a Model

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

If you have an array of Tag model IDs (primary keys) already, 
you can tag a model using those IDs instead of the tag names:

```php
// assuming no other tags exist yet ...
$model->tag('Apple','Banana','Cherry','Durian');

$newModel->tagById([1,3]);
// ... $newModel is tagged with "Apple" and "Cherry"
```

Similarly, you can untag by ID as well:

```php
// assuming no other tags exist yet ...
$model->tag('Apple','Banana','Cherry','Durian');

$model->untagById([1,3]);
// ... $model is now only tagged with "Banana" and "Durian"
```

Tagging/untagging/retagging by ID is useful if you have, for instance, a
form with a multi-select dropdown or a list of checkboxes of all tags, e.g.:

```html
<select name="tags" multiple>
  <option value="1">Apple</option>
  <option value="2">Banana</option>
  <option value="3">Cherry</option>
  ... etc.
</select>
```

When the form submits, the data sent to your controller is an array
of all the selected tag IDs.  It is then easy to update the model 
accordingly with the selected tags:

```php
$tags = $request->input('tags');
$model->retagById($tags);
```


### Working with a Model's Tags

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

You can also see if a model has a certain tag:

```php
$model->tag('Apple,Banana,Cherry');

// tests use the normalized tag name

var_dump($model->hasTag('apple'));
// bool(true)

var_dump($model->hasTag('Durian'));
// bool(false)
```


## Query Scopes

For reference, imagine the following models have been tagged:

| Model Id | Tags                  |
|:--------:|-----------------------|
|    1     | - no tags -           |
|    2     | apple                 |
|    3     | apple, banana         |
|    4     | apple, banana, cherry |
|    5     | cherry                |
|    6     | apple, durian         |
|    7     | banana, durian        |
|    8     | apple, banana, durian |


You can easily find models with tags through some query scopes:

```php
// Find models that are tagged with all the given tags
// i.e. everything tagged "Apple AND Banana".
// (returns models with Ids: 3, 4, 8)

Model::withAllTags('Apple,Banana')->get();

// Find models with any one of the given tags
// i.e. everything tagged "Apple OR Banana".
// (returns Ids: 2, 3, 4, 6, 7, 8)

Model::withAnyTags('Apple,Banana')->get();

// Find models that have any tags
// (returns Ids: 2, 3, 4, 5, 6, 7, 8)

Model::isTagged()->get();
```

And the inverse:

```php
// Find models that are not tagged with all the given tags,
// i.e. everything not tagged "Apple AND Banana".
// (returns models with Ids: 2, 5, 6, 7)

Model::withoutAllTags('Apple,Banana')->get();

// To also include untagged models, pass another parameter:
// (returns models with Ids: 1, 2, 5, 6, 7)

Model::withoutAllTags('Apple,Banana', true)->get();

// Find models without any one of the given tags
// i.e. everything not tagged "Apple OR Banana".
// (returns Ids: 5)

Model::withoutAnyTags('Apple,Banana')->get();

// To also include untagged models, pass another parameter:
// (returns models with Ids: 1, 5)

Model::withoutAnyTags('Apple,Banana', true)->get();

// Find models that have no tags
// (returns Ids: 1)

Model::isNotTagged()->get();
```

Some edge-case examples:

```php
// Passing an empty tag list to a scope either throws an
// exception or returns nothing, depending on the
// "throwEmptyExceptions" configuration option

Model::withAllTags('');
Model::withAnyTags('');

// Returns nothing, because the "Fig" tag doesn't exist
// so no model has that tag

Model::withAllTags('Apple,Fig');
```

Combining scopes:

```php
// Find models with any one of the given tags
// i.e. everything tagged "Apple OR Banana"
// but without one of the given tags 
// i.e. everything NOT tagged "Cherry".
// (returns Ids: 2, 3, 6, 7, 8)

Model::withAnyTags('Apple,Banana')::withoutAnyTags('Cherry')->get();

// Find models that are not tagged with all the given tags,
// i.e. everything not tagged "Apple AND Banana".
// and models without any one of the given tags
// i.e. everything not tagged "Cherry OR Durian".
// (returns models with Ids: 2)

Model::withoutAllTags('Apple,Banana')::withoutAnyTags('Cherry,Durian')->get();

// Find models with any one of the given tags
// i.e. everything tagged "Apple OR Banana".
// AND tagged "Cherry OR Durian".
// (returns Ids: 4, 6, 7, 8)

Model::withAnyTags('Apple,Banana')::withAnyTags('Cherry,Durian')->get();
```

Finally, you can easily find all the tags used across all instances of a model:

```php
// Returns an array of tag names used by all Model instances
// e.g.: ['apple','banana','cherry','durian']

Model::allTags();

// Same as above, but as a delimited list
// e.g. 'apple,banana,cherry,durian'

Model::allTagsList();

// Returns a collection of all the Tag models used by any Model instances

Model::allTagModels();
```


## Events

You can create a listener to handle when a model is tagged:

```php
// in your EventServiceProvider 

use Cviebrock\EloquentTaggable\Events\ModelTagged;
...
protected $listen = [
    ...
    ModelTagged::class => [
        ReactModelTagged::class  // your Listener class
    ]
    ...
];
```

The listener receives the `Cviebrock\EloquentTaggable\Events\ModelTagged` event
with the model and tags:

```php
namespace App\Listeners;

use Cviebrock\EloquentTaggable\Events\ModelTagged;

class ReactModelTagged
{
    /**
     * Handle the event.
     *
     * @param  ModelTagged  $event
     * @return void
     */
    public function handle(ModelTagged $event)
    {
        dd($event->getModel(), $event->getTags());
    }
}
```

You can use also listen for the `Cviebrock\EloquentTaggable\Events\ModelUntagged` event
which is fired when a tag is removed.


## Other Methods

You can rename a tag for your model:

```php
Model::rename('Apple', 'Apricot');
```

This will only affect instances of `Model` that were tagged "Apple".  If another model was also tagged
"Apple", those tags won't be renamed.  (To rename a tag across all models, see the example below under
the [TagService Class](#the-tagservice-class).)

You can also get a list of popular tags for your model (including the model count):

```php
$tags = Model::popularTags($limit);
$tags = Model::popularTagsNormalized($limit);

// Will return an array like:
//
// [
//     'apple' => 5,
//     'banana' => 3,
//     'durian' => 3,
//     'cherry' => 2,
// ]
```

You can also provide a minimum count (i.e., only return tags that have been used 3 or more times):

```php
$tags = Model::popularTags($limit, 3);
```

(Again, the above will limit the query to one particular model.  To get a list of
popular tag across all models, see the example below under the [TagService Class](#the-tagservice-class).)


## The Tag Model

There are a few methods you can run on the Tag model itself.

`Tag::findByName('Apple')` will return the Tag model for the given name.  This can
then be chained to find all the related models.

Under the hood, the above uses a `byName()` query scope on the Tag model, which you
are also free to use if you want to write a custom query.


## The TagService Class

You can also use `TagService` class directly, however almost all the functionality is
exposed via the various methods provided by the trait, so you probably don't need to.

```php
// Instantiate the service (can also be done via dependency injection)
$tagService = app(\Cviebrock\EloquentTaggable\Services\TagService::class);

// Return a collection of all the Tag models used by \App\Model instances
// (same as doing \App\Model::allTagModels() ):

$tagService->getAllTags(\App\Model);

// Return a collection of all the Tag models used by all models:

$tagService->getAllTags();

// Rename all tags from "Apple" to "Apricot" for the \App\Model uses
// (same as doing \App\Model::renameTag("Apple", "Apricot") ):

$tagService->renameTags("Apple", "Apricot", \App\Model);

// Rename all tags from "Apple" to "Apricot" across all models:

$tagService->renameTags("Apple", "Apricot");

// Get the most popular tags across all models, or for just one model:

$tagService->getPopularTags();
$tagService->getPopularTags($limit);
$tagService->getPopularTags($limit, \App\Model);
$tagService->getPopularTags($limit, \App\Model, $minimumCount);

// Find all the tags that aren't used by any model:

$tagService->getAllUnusedTags();
```

As always, take a look at the code for full documentation of the service class.


## Configuration

Configuration is handled through the settings in `/app/config/taggable.php`.  The default values are:

```php
return [
    'delimiters'           => ',;',
    'glue'                 => ',',
    'normalizer'           => 'mb_strtolower',
    'connection'           => null,
    'throwEmptyExceptions' => false,
    'taggedModels'         => [],
    'model'                => \Cviebrock\EloquentTaggable\Models\Tag::class,
    'tables' => [
        'taggable_tags'      => 'taggable_tags',
        'taggable_taggables' => 'taggable_taggables',
    ],
];
```

### delimiters

These are the single-character strings that can delimit the list of tags passed to the `tag()` method.
By default, it's just the comma, but you can change it to another character, or use multiple characters.

For example, if __delimiters__ is set to ";,/", then this will work as expected:

```php
$model->tag('Apple/Banana;Cherry,Durian');

// $model will have four tags
```

### glue

When building a string for the `tagList` attribute, this is the "glue" that is used to join tags.
With the default values, in the above case:

```php
var_dump($model->tagList);

// string 'Apple,Banana,Cherry,Durian' (length=26)
```

### normalizer

Each tag is "normalized" before being stored in the database.  This is so that variations in the
spelling or capitalization of tags don't generate duplicate tags.  For example, we don't want three
different tags in the following case:

```php
$model->tag('Apple');
$model->tag('APPLE');
$model->tag('apple');
```

Normalization happens by passing each tag name through a normalizer function.  By default, this is
PHP's `mb_strtolower()` function, but you can change this to any function or callable that takes a
single string value and returns a string value.  Some ideas:

```php

    // default normalization
    'normalizer' => 'mb_strtolower',

    // same result, but using a closure
    'normalizer' => function($string) {
        return mb_strtolower($string);
    },

    // using a class method
    'normalizer' => ['Illuminate\Support\Str', 'slug'],
```

You can access the normalized values of the tags through `$model->tagListNormalized` and
`$model->tagArrayNormalized`, which work identically to `$model->tagList` and `$model->tagArray`
(described above) except that they return the normalized values instead.

And you can, of course, access the normalized name directly from a tag:

```php
echo $tag->normalized;
```

### connection

You can set this to specify that the Tag model should use a different database connection.
Otherwise, it will use the default connection (i.e. from `config('database.default')`).

### throwEmptyExceptions

Passing empty strings or arrays to any of the scope methods is an interesting situation.
Logically, you can't get a list of models that have all or any of a list of tags ... if the list is empty!

By default, the `throwEmptyExceptions` is set to false.  Passing an empty value to a query scope
will "short-circuit" the query and return no models.  This makes your application code cleaner,
so you don't need to check for empty values before calling the scope.

However, if `throwEmptyExceptions` is set to true, then passing an empty value to the scope will
throw a `Cviebrock\EloquentTaggable\Exceptions\NoTagsSpecifiedException` exception in these cases.
You can then catch the exception in your application code and handle it however you like.

### taggedModels

If you want to be able to find all the models that share a tag, you will need
to define the inverse relations here.  The array keys are the relation names
you would use to access them (e.g. `posts`) and the values are the qualified
class names of the models that are taggable (e.g. `\App\Post`).  e.g. with
the following configuration:

```php
'taggedModels' => [
    'posts' => \App\Post::class
]
```

You will be able to do:

```php
$posts = Tag::findByName('Apple')->posts;
```

This will return a collection of all the Posts that are tagged "Apple".

### model

By default, the package will use its own model class for Tags.  If you want to
use your own customized Tag model, then extend the package's class with
your own class, and update the configuration to reference your model.

### tables

By default, the package will create two tables to store the tag information.
If you want to use different table names, then change these two values.  The 
model, service, and migration classes will all read the configuration values.



## Bugs, Suggestions, Contributions and Support

Thanks to [everyone](https://github.com/cviebrock/eloquent-taggable/graphs/contributors)
who has contributed to this project, with a big shout-out to
[Michael Riediger](https://stackoverflow.com/users/502502/riedsio) for helping optimize the SQL.

Please use [GitHub](https://github.com/cviebrock/eloquent-taggable) for reporting bugs,
and making comments or suggestions.

See [CONTRIBUTING.md](CONTRIBUTING.md) for how to contribute changes.


## Copyright and License

[eloquent-taggable](https://github.com/cviebrock/eloquent-taggable)
was written by [Colin Viebrock](http://viebrock.ca) and is released under the
[MIT License](LICENSE.md).

Copyright (c) 2013 Colin Viebrock
