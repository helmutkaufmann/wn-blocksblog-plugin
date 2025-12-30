<?php namespace Mercator\BlocksBlog\Components;

use Cms\Classes\ComponentBase;
use Mercator\BlocksBlog\Models\Category;

class BlogCategories extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Blog Categories',
            'description' => 'Lists all categories and links/jumps to the blog posts block.',
        ];
    }

    public function defineProperties()
    {
        return [
            'anchorId' => [
                'title' => 'Anchor id for jump',
                'type' => 'string',
                'default' => 'blog',
            ],
            'linkMode' => [
                'title' => 'Link mode',
                'type' => 'dropdown',
                'default' => 'query',
                'options' => [
                    'query' => 'Link to ?category=slug#anchor',
                    'ajax' => 'Filter via AJAX (requires blogPosts on same page)',
                ],
            ],
        ];
    }

    public function onRun()
    {
        $this->page['anchorId'] = $this->property('anchorId');
        $this->page['categories'] = Category::orderBy('name')->get();
        $this->page['linkMode'] = $this->property('linkMode');
    }
}
