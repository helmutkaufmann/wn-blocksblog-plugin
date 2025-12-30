<?php namespace Mercator\BlocksBlog\Models;

use Model;

class Category extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sluggable;

    public $table = 'mercator_blocksblog_categories';

    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:mercator_blocksblog_categories',
    ];

    protected $slugs = [
        'slug' => 'name',
    ];

    protected $fillable = ['name', 'slug', 'description'];

    public $belongsToMany = [
        'posts' => [
            \Mercator\BlocksBlog\Models\Post::class,
            'table' => 'mercator_blocksblog_post_categories',
            'order' => 'published_at desc',
        ],
    ];
}
