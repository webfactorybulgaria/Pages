<?php

namespace TypiCMS\Modules\Pages\Models;

use TypiCMS\Modules\Core\Shells\Traits\Translatable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laracasts\Presenter\PresentableTrait;
use TypiCMS\Modules\Core\Shells\Models\Base;
use TypiCMS\Modules\History\Shells\Traits\Historable;
use TypiCMS\NestableTrait;

class Page extends Base
{
    use Historable;
    use Translatable;
    use PresentableTrait;
    use NestableTrait;

    protected $presenter = 'TypiCMS\Modules\Pages\Shells\Presenters\ModulePresenter';

    protected $fillable = [
        'meta_robots_no_index',
        'meta_robots_no_follow',
        'position',
        'parent_id',
        'private',
        'is_home',
        'redirect',
        'no_cache',
        'css',
        'js',
        'module',
        'template',
        'image',
        'system_name',
        // Translatable columns
        'title',
        'slug',
        'uri',
        'status',
        'body',
        'meta_keywords',
        'meta_description',
        'meta_tags',
    ];

    /**
     * Translatable model configs.
     *
     * @var array
     */
    public $translatedAttributes = [
        'title',
        'slug',
        'uri',
        'status',
        'body',
        'meta_keywords',
        'meta_description',
        'meta_tags',
    ];

    protected $appends = ['thumb'];

    /**
     * Is this page cacheable?
     *
     * @return bool
     */
    public function cacheable()
    {
        return !$this->no_cache;
    }

    /**
     * Get front office uri.
     *
     * @param string $locale
     *
     * @return string
     */
    public function uri($locale = null)
    {
        $locale = $locale ?: config('app.locale');
        if (!$this->hasTranslation($locale)) {
            return;
        }
        if ($locale != config('app.locale')) {
            $translated = $this->translate($locale);
            if ($translated->status) {
                $uri = $translated->uri;
            } else {
                $uri = null;
            }
        } else {
            $uri = $this->uri;
        }
        if (
            config('app.fallback_locale') != $locale ||
            config('typicms.main_locale_in_url')
        ) {
            $uri = $uri ? $locale.'/'.$uri : $locale;
        }

        return $uri ?: '/';
    }

    /**
     * Append thumb attribute.
     *
     * @return string
     */
    public function getThumbAttribute()
    {
        return $this->present()->thumbSrc(null, 22);
    }

    /**
     * A page can have menulinks.
     */
    public function menulinks()
    {
        return $this->hasMany('TypiCMS\Modules\Menulinks\Shells\Models\Menulink');
    }

    /**
     * A page has many galleries.
     *
     * @return MorphToMany
     */
    public function galleries()
    {
        return $this->morphToMany('TypiCMS\Modules\Galleries\Shells\Models\Gallery', 'galleryable')
            ->withPivot('position')
            ->orderBy('position')
            ->withTimestamps();
    }

    /**
     * A page can have children.
     */
    public function children()
    {
        return $this->hasMany('TypiCMS\Modules\Pages\Shells\Models\Page', 'parent_id')->order();
    }

    /**
     * A page can have a parent.
     */
    public function parent()
    {
        return $this->belongsTo('TypiCMS\Modules\Pages\Shells\Models\Page', 'parent_id');
    }
}
