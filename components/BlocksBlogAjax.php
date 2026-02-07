<?php namespace Mercator\BlocksBlog\Components;

use Cms\Classes\ComponentBase;
use Mercator\BlocksBlog\Models\Post;

class BlocksBlogAjax extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'BlocksBlog AJAX',
            'description' => 'AJAX handlers for Winter.Blocks blog blocks',
        ];
    }

    public function onLoadMore()
    {
        $anchorId = trim((string) post('anchorId', 'blog'));

        $page     = (int) post('page', 1) + 1;
        $perPage  = (int) post('perPage', 3);
        $take     = (int) post('loadMore', 5);

        $categorySlugsRaw = trim((string) post('categorySlugs', ''));
        $slugs = array_filter(array_map('trim', explode(',', $categorySlugsRaw)));

        $category = trim((string) post('category', ''));

        // skip already shown: perPage + (page-2)*take
        $skip = $perPage + (($page - 2) * $take);

        $q = Post::published()
            ->with(['categories','author'])
            ->orderBy('published_at', 'desc');

        if ($category !== '') {
            $q->whereHas('categories', fn($qq) => $qq->where('slug', $category));
        } elseif (!empty($slugs)) {
            $q->whereHas('categories', fn($qq) => $qq->whereIn('slug', $slugs));
        }

        $posts = (clone $q)->skip($skip)->take($take)->get();
        $hasMore = (clone $q)->count() > ($skip + $posts->count());

        // variables for the partial rendered via data-request-update
        $this->page['posts'] = $posts;

        $formSel = '#bb-form-' . $anchorId;

        return [
            $formSel . ' input[name=page]' => ['value' => $page],
            $formSel . ' .bb-loadmore-wrap' => $this->renderPartial(
                'mercator/blocksblog::blocks/_loadmore',
                ['hasMore' => $hasMore, 'anchorId' => $anchorId]
            ),
        ];
    }
}