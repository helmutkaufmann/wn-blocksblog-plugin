<?php namespace Mercator\BlocksBlog\Components;

use Cms\Classes\ComponentBase;
use Mercator\BlocksBlog\Models\Post;
use Log;

class BlogPosts extends ComponentBase
{
    
    public function componentDetails()
    {
        return [
            'name' => 'Blog Posts (Blocks)',
            'description' => 'Lists posts with AJAX load-more and read-more using blocks fields.',
        ];
    }

    public function defineProperties()
    {
        return [
            'perPage' => [
                'title' => 'Initial posts (X)',
                'type' => 'string',
                'default' => "5",
            ],
            'loadMore' => [
                'title' => 'Load more amount (Y)',
                'type' => 'string',
                'default' => "5",
            ],
            'categorySlugs' => [
                'title' => 'Filter categories (slugs, comma-separated)',
                'type' => 'string',
                'default' => '',
            ],
            'anchorId' => [
                'title' => 'Anchor id for jump',
                'type' => 'string',
                'default' => 'blog',
            ],
            'authorId' => [
                'title' => 'Author ID (optional)',
                'type' => 'string',
                'default' => '',
            ],
            'showFeaturedImage' => [
                'title' => 'Show featured image',
                'type' => 'checkbox',
                'default' => true,
            ],
            'fallbackFeaturedImage' => [
                'title' => 'Fallback featured image (Media Library path)',
                'type' => 'string',
                'default' => '',
            ],        ];
    }
    
    public $posts;
    public $hasMore;
    public $pageNum;
    public $anchorId;
    public $currentCategory;
    public $showFeaturedImage;
    public $fallbackFeaturedImage;
    public $featuredImageShape;

    public function onRun()
    {
        // Authors that have at least one published post (for frontend filters)
        $this->page['activeAuthors'] = \Backend\Models\User::whereHas('blocksblog_posts', function($q) {
            $q->published();
        })->orderBy('first_name')->get();

        $this->page['currentAuthor'] = (int) input('author', (int) $this->property('authorId'));

        $this->anchorId = $this->property('anchorId');
        $this->currentCategory = trim((string) input('category'));
        $this->showFeaturedImage = (bool) $this->property('showFeaturedImage');
        $this->fallbackFeaturedImage = (string) $this->property('fallbackFeaturedImage');
        $this->featuredImageShape = (string) $this->property('featuredImageShape');
$query = $this->queryPosts();

        $this->posts = (clone $query)->take((int)$this->property('perPage'))->get();
        $this->pageNum = 1;
        $this->hasMore = (clone $query)->count() > (int)$this->property('perPage');

        // keep page vars for your existing partials/AJAX
        $this->page['anchorId'] = $this->anchorId;
        $this->page['currentCategory'] = $this->currentCategory;
        $this->page['posts'] = $this->posts;
        $this->page['pageNum'] = $this->pageNum;
        $this->page['hasMore'] = $this->hasMore;
        $this->page['showFeaturedImage'] = $this->showFeaturedImage;
        $this->page['fallbackFeaturedImage'] = $this->fallbackFeaturedImage;
        $this->page['featuredImageShape'] = $this->featuredImageShape;
}

    protected function selectedCategorySlugs()
    {
        $slugs = array_filter(array_map('trim', explode(',', (string)$this->property('categorySlugs'))));
        $param = trim((string) input('category'));
        if ($param !== '') {
            $slugs = array_filter(array_map('trim', explode(',', $param)));
        }
        return $slugs;
    }

    protected function queryPosts($categorySlug = null)
    {
        $q = Post::published()->with(['categories','author'])->orderBy('published_at','desc');

        $slugs = $this->selectedCategorySlugs();
        if ($categorySlug) $slugs = [$categorySlug];

        if (!empty($slugs)) {
            $q->whereHas('categories', function($qq) use ($slugs) {
                $qq->whereIn('slug', $slugs);
            });
        }

        // Filter by author (either URL ?author=ID or component property authorId)
        $authorId = (int) input('author', (int) $this->property('authorId'));
        if ($authorId > 0) {
            $q->where('author_id', $authorId);
        }

        return $q;
    }

    public function onLoadMore()
    {
        // Next "page" in the incremental load-more flow
        $page     = (int) post('page', 1) + 1;
        $category = trim((string) post('category'));

        $perPage = (int) $this->property('perPage');
        $take    = (int) $this->property('loadMore');

        // Skip already shown: perPage + (page-2)*take
        $skip = $perPage + (($page - 2) * $take);

        $query = $this->queryPosts($category ?: null);

        $posts   = (clone $query)->skip($skip)->take($take)->get();
        $hasMore = (clone $query)->count() > ($skip + $posts->count());

        $anchorId = $this->property('anchorId');

        // Return HTML fragments; the frontend appends them (works even if update-mode isn't supported)
        return [
            'page' => $page,
            'postsHtml' => $this->renderPartial('@posts_items', [
                'posts' => $posts,
                'anchorId' => $anchorId,
                'showFeaturedImage' => (bool) $this->property('showFeaturedImage'),
                'fallbackFeaturedImage' => (string) $this->property('fallbackFeaturedImage'),
                'featuredImageShape' => (string) $this->property('featuredImageShape'),
]),
            'loadmoreHtml' => $this->renderPartial('@loadmore', [
                'hasMore' => $hasMore,
                'anchorId' => $anchorId,
            ]),
        ];
    }

    public function onReadMore()
{
    $id = (int) post('id');
    $post = Post::published()->with(['categories','author'])->find($id);
    if (!$post) return [];
    return [
        '#bb-post-'.$post->id => $this->renderPartial('@post_full', [
            'post' => $post,
            'anchorId' => $this->property('anchorId'),
            'showFeaturedImage' => (bool) $this->property('showFeaturedImage'),
            'fallbackFeaturedImage' => (string) $this->property('fallbackFeaturedImage'),
                'featuredImageShape' => (string) $this->property('featuredImageShape'),
]),
    ];
}

    public function onFilterCategory()
    {
        $slug = trim((string) post('slug'));
        $posts = $this->queryPosts($slug ?: null)->take((int)$this->property('perPage'))->get();
        $hasMore = $this->queryPosts($slug ?: null)->count() > (int)$this->property('perPage');

        return [
  '#'.$this->property('anchorId').' .bb-posts' => $this->renderPartial('@posts_list', [
      'posts' => $posts,
      'pageNum' => 1,
      'hasMore' => $hasMore,
      'anchorId' => $this->property('anchorId'),
      'currentCategory' => $slug,
      'showFeaturedImage' => (bool) $this->property('showFeaturedImage'),
      'fallbackFeaturedImage' => (string) $this->property('fallbackFeaturedImage'),
                'featuredImageShape' => (string) $this->property('featuredImageShape'),
]),
];
    }
}
