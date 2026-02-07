<?php namespace Mercator\BlocksBlog;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Blocks Blog',
            'description' => 'Blog with categories and posts using Blocks fields',
            'author' => 'Mercator',
            'icon' => 'icon-newspaper-o'
        ];
    }

    public function registerComponents()
{
    return [
        \Mercator\BlocksBlog\Components\BlogPosts::class => 'blogPosts',
        \Mercator\BlocksBlog\Components\BlogCategories::class => 'blogCategories',
        \Mercator\BlocksBlog\Components\BlocksBlogAjax::class => 'blocksBlogAjax',
    ];
}

    public function registerBlocks(): array
    {
        return [
            'blog_posts_list' => '$/mercator/blocksblog/blocks/blog_posts_list.block',
        ];
    }


public function boot()
{
    
    \Winter\Pages\Classes\Page::extend(function($page) {
        $page->addDynamicMethod('getCategorySlugsOptions', function() {
            return \Mercator\BlocksBlog\Models\Category::query()
                ->orderBy('name')
                ->pluck('name', 'slug')
                ->all();
        });
    });

\Backend\Models\User::extend(function($model) {

        // LastName FirstName for dropdown / lists
        $model->addDynamicMethod('getNameLfAttribute', function() use ($model) {
            $last  = trim($model->last_name ?? '');
            $first = trim($model->first_name ?? '');
            $name  = trim($last . ' ' . $first);
            return $name !== '' ? $name : ($model->login ?? '');
        });

        // Sort by last name then first name
        $model->addDynamicMethod('scopeOrderByName', function($query) {
            return $query->orderBy('last_name')->orderBy('first_name');
        });
    });
    

    \Backend\Models\User::extend(function($model) {
        $model->hasMany['blocksblog_posts'] = [
            \Mercator\BlocksBlog\Models\Post::class,
            'key' => 'author_id'
        ];
    });
}

}
