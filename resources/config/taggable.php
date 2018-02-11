<?php

return [

    /**
     * List of characters that can delimit the tags passed to the
     * tag() / untag() / etc. functions.
     */
    'delimiters'           => ',;',

    /**
     * Character used to delimit tag lists returned in the
     * tagList, tagListNormalized, etc. attributes.
     */
    'glue'                 => ',',

    /**
     * Method used to "normalize" tag names.  Can either be a global function name,
     * a closure function, or a callable, e.g. ['Classname', 'method'].
     */
    'normalizer'           => 'mb_strtolower',

    /**
     * The database connection to use for the Tag model and associated tables.
     * By default, we use the default database connection, but this can be defined
     * so that all the tag-related tables are stored in a different connection.
     */
    'connection'           => null,

    /**
     * How to handle passing empty values to the scope queries.  When set to false,
     * the scope queries will return no models.  When set to true, passing an empty
     * value to the scope queries will throw an exception instead.
     */
    'throwEmptyExceptions' => false,

    /**
     * If you want to be able to find all the models that share a tag, you will need
     * to define the inverse relations here.  The array keys are the relation names
     * you would use to access them (e.g. "posts") and the values are the qualified
     * class names of the models that are taggable (e.g. "\App\Post).  e.g. with
     * the following configuration:
     *
     *  'taggedModels' => [
     *      'posts' => \App\Post::class
     *  ]
     *
     * You will be able to do:
     *
     *  $posts = Tag::findByName('Apple')->posts;
     *
     * to get a collection of all the Posts that are tagged "Apple".
     */

    'taggedModels' => [],

    /**
     * The model used to store the tags in the database.  You can
     * create your own class that extends the package's Tag model,
     * then update the configuration below.
     */
    'model'  => \Cviebrock\EloquentTaggable\Models\Tag::class,
];
