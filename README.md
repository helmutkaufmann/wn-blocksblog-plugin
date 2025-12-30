
# Blocks Blog (Mercator.BlocksBlog)

A lightweight blog plugin for WinterCMS that stores post Excerpt and Content as Winter Blocks fields, supports categories, and provides a frontend component with AJAX load-more, read-more, and author/category filtering.

⸻

## Features
•	Posts with:
•	title, slug
•	excerpt (Blocks field)
•	content (Blocks field)
•	is_published, published_at
•	author (Backend User)
•	Categories with slug support
•	Frontend component:
•	Initial list
•	Load-more (AJAX)
•	Read-more (AJAX partial replacement)
•	Filter by category (AJAX)
•	Filter by author (AJAX)
•	List all authors with active posts
•	Backend:
•	unpublished badge (optional)
•	delete selected (bulk actions)

⸻

## Frontend usage

1) Add the component to a page / layout

In a CMS page:

title = "Blog"
url = "/blog/:slug?"
layout = "default"

[blogPosts]
perPage = 5
loadMore = 5
anchorId = "blog"
==

Then render it:

{% component 'blogPosts' %}

⸻

##  Using it inside Winter Blocks content / excerpts

If you are rendering blocks from excerpt or content, you can render them like:

{{ renderBlocks(post.excerpt) }}
{{ renderBlocks(post.content) }}

If you are inside a component partial and __SELF__ is available:

{% partial __SELF__ ~ '::_blocks' value=post.excerpt %}

⸻

## Output list manually (no component partials)

If you want to do your own markup:

```
{% component 'blogPosts' %}

{% set posts = __SELF__.posts %}
{% set hasMore = __SELF__.hasMore %}
{% set pageNum = __SELF__.pageNum %}

<ul>
  {% for post in posts %}
    <li>
      <a href="/blog/{{ post.slug }}">{{ post.title }}</a>
      {% if post.author %}
        <small>– {{ post.author.last_name }} {{ post.author.first_name }}</small>
      {% endif %}
    </li>
  {% endfor %}
</ul>

{% if hasMore %}
  <button
    class="uk-button uk-button-default"
    data-request="{{ __SELF__ }}::onLoadMore"
    data-request-data="page: {{ pageNum }}, category: '{{ __SELF__.currentCategory }}'">
    Load more
  </button>
{% endif %}
```

⸻

## Filter by category (AJAX)

Render category links:
```
{% for c in post.categories %}
  <a href="javascript:;"
     data-request="{{ __SELF__ }}::onFilterCategory"
     data-request-data="slug: '{{ c.slug }}'">
    {{ c.name }}
  </a>
{% endfor %}
```
The handler updates the posts list region (configured in your component partials).

⸻

## Filter by author (AJAX)

Show a dropdown of active authors

In your page or partial:
```
{% set authors = __SELF__.authors %}

<select
  class="uk-select"
  data-request="{{ __SELF__ }}::onFilterAuthor"
  data-request-data="anchorId: '{{ __SELF__.anchorId }}'">
  <option value="">All authors</option>
  {% for a in authors %}
    <option value="{{ a.id }}">
      {{ a.last_name }} {{ a.first_name }}
    </option>
  {% endfor %}
</select>
```
⸻

## Limit posts per author (server-side)

If you want to show only posts by a given author:

```
[blogPosts]
perPage = 10
loadMore = 5
authorId = 3
```

Then in your component query:
```
if ($authorId = $this->property('authorId')) {
    $q->where('author_id', $authorId);
}
```
⸻

## License

MIT License

Copyright (C) 2025 Helmut Kaufmann, software@mercator.li

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
