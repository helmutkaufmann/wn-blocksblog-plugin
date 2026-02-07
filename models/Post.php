<?php namespace Mercator\BlocksBlog\Models;

use Model;
use BackendAuth;
use System\Models\File;

class Post extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sluggable;

    public $table = "mercator_blocksblog_posts";

    protected $dates = ["published_at"];

    public $rules = [
        "title" => "required",
        "slug" => "required|unique:mercator_blocksblog_posts",
    ];

    protected $slugs = [
        "slug" => "title",
    ];

    protected $casts = [
        "excerpt" => "array",
        "content" => "array",
        "is_published" => "boolean",
        "published_at" => "datetime",
    ];

    protected $fillable = ["title", "slug", "excerpt", "content", "is_published", "published_at", "author_id"];

    public $belongsTo = [
        "author" => [\Backend\Models\User::class, "key" => "author_id"],
    ];

    public $belongsToMany = [
        "categories" => [
            \Mercator\BlocksBlog\Models\Category::class,
            "table" => "mercator_blocksblog_post_categories",
            "order" => "name",
        ],
    ];

    /**
     * Post-specific image uploaded via backend form. Stored as a System File attachment
     * (not in the Media Library / media finder).
     */
    public $attachOne = [
        'featured_image' => File::class,
    ];

    public function beforeCreate()
    {
        if (!$this->author_id && class_exists("BackendAuth")) {
            $user = BackendAuth::getUser();
            if ($user) {
                $this->author_id = $user->id;
            }
        }
    }

    public function scopePublished($query)
    {
        return $query->where("is_published", true)->whereNotNull("published_at")->where("published_at", "<=", now());
    }

    public function beforeValidate()
    {
        // if published -> published_at must be set
        if ($this->is_published) {
            $this->rules["published_at"] = "required|date";
        } else {
            // not required if not published
            unset($this->rules["published_at"]);
        }
    }

    /**
     * Computed full name of the backend author user.
     * Available as $post->author_full_name (Twig: post.author_full_name).
     */
    public function getAuthorFullNameAttribute(): string
    {
        $u = $this->author;
        if (!$u) {
            return "";
        }

        $name = trim(($u->first_name ?? "") . " " . ($u->last_name ?? ""));
        return $name !== "" ? $name : $u->full_name ?? ($u->login ?? "");
    }

    /**
     * Backend dropdown options for author_id.
     * Shows "LastName FirstName" sorted by last name then first name.
     */
    public function getAuthorIdOptions(): array
    {
        $users = \Backend\Models\User::orderBy("last_name")->orderBy("first_name")->get();
        $out = [];
        foreach ($users as $u) {
            $label = trim(($u->last_name ?? "") . " " . ($u->first_name ?? ""));
            if ($label === "") {
                $label = $u->full_name ?? ($u->login ?? "User #" . $u->id);
            }
            $out[$u->id] = $label;
        }
        return $out;
    }
    
    public function getAuthorNameAttribute(): string
    {
        if (!$this->author) {
            return '';
        }

        $last  = $this->author->last_name ?? '';
        $first = $this->author->first_name ?? '';

        return trim($last . ' ' . $first);
}
}
