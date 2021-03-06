<?php

namespace TypiCMS\Modules\Pages\Repositories;

use Illuminate\Support\Facades\Request;
use TypiCMS\Modules\Core\Shells\Repositories\CacheAbstractDecorator;
use TypiCMS\Modules\Core\Shells\Services\Cache\CacheInterface;

class CacheDecorator extends CacheAbstractDecorator implements PageInterface
{
    public function __construct(PageInterface $repo, CacheInterface $cache)
    {
        $this->repo = $repo;
        $this->cache = $cache;
    }

    /**
     * Get a page by its uri.
     *
     * @param string $uri
     * @param string $locale
     * @param array  $with
     *
     * @return TypiCMS\Modules\Models\Shells\Page $model
     */
    public function getFirstByUri($uri, $locale, array $with = [])
    {
        $cacheKey = md5($this->cachePrefix().'getFirstByUri.'.$uri.$locale.serialize($with).serialize(Request::all()));

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $model = $this->repo->getFirstByUri($uri, $locale, $with);

        // Store in cache for next request
        $this->cache->put($cacheKey, $model);

        return $model;
    }

    /**
     * Get submenu for a page.
     *
     * @return Collection
     */
    public function getSubMenu($uri, $all = false)
    {
        $cacheKey = md5($this->cachePrefix().'getSubMenu.'.$uri.$all);

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $models = $this->repo->getSubMenu($uri, $all);

        // Store in cache for next request
        $this->cache->put($cacheKey, $models);

        return $models;
    }

    /**
     * Get pages linked to module to build routes.
     *
     * @return array
     */
    public function getForRoutes()
    {
        $cacheKey = md5($this->cachePrefix().'getForRoutes');

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $models = $this->repo->getForRoutes();

        // Store in cache for next request
        $this->cache->put($cacheKey, $models);

        return $models;
    }

    /**
     * Get all translated pages for a select/options.
     *
     * @return array
     */
    public function allForSelect()
    {
        return $this->repo->allForSelect();
    }

    /**
     * Get all translated pages for a menu tree.
     *
     * @return array
     */
    public function allForTreeMap()
    {

        $cacheKey = md5($this->cachePrefix().'allForTreeMap');

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        // Item not cached, retrieve it
        $models = $this->repo->allForTreeMap();

        // Store in cache for next request
        $this->cache->put($cacheKey, $models);

        return $models;
    }

}
