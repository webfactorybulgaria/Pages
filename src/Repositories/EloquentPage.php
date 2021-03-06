<?php

namespace TypiCMS\Modules\Pages\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use TypiCMS\Modules\Core\Shells\Repositories\RepositoriesAbstract;

class EloquentPage extends RepositoriesAbstract implements PageInterface
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Update an existing model.
     *
     * @param array  Data needed for model update
     *
     * @return bool
     */
    public function update(array $data, array $syncTables = [])
    {
        $model = $this->model->find($data['id']);

        $model->fill($data);

        array_push($syncTables, 'galleries');

        foreach ($syncTables as $table) {
            $this->syncRelation($model, $data, $table);
        }

        $langsChanged = [];
        foreach ($model->translations as $translation) {
            if (isset($translation->getDirty()['slug'])) {
                $langsChanged[$translation->locale] = true;
            }
        }

        if ($model->save()) {
            if (!empty($langsChanged)) {
                event('page.resetChildrenUri', [$model, $langsChanged]);
            }

            return true;
        }

        return false;
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
        $model = $this->make($with)
            ->where(function (Builder $query) use ($uri, $locale) {
                $query->where('uri', $uri)
                    ->where('locale', $locale);
                if (!Request::input('preview')) {
                    $query->where('status', 1);
                }
            })
            ->firstOrFail();

        return $model;
    }

    /**
     * Get submenu for a page.
     *
     * @return Collection
     */
    public function getSubMenu($uri, $all = false)
    {
        $rootUriArray = explode('/', $uri);
        $uri = $rootUriArray[0];
        if (in_array($uri, config('translatable.locales'))) {
            if (isset($rootUriArray[1])) { // i
                $uri .= '/'.$rootUriArray[1]; // add next part of uri in locale
            }
        }

        $query = $this->model
            ->select('*')
            ->addSelect('pages.id AS id')
            ->where('uri', '!=', $uri)
            ->where('uri', 'LIKE', $uri.'%');

        if (!$all) {
            $query->where('status', 1);
        }
        $query->where('locale', config('app.locale'));

        $models = $query->order()->get()->nest();

        return $models;
    }

    /**
     * Get pages linked to a module.
     *
     * @return array
     */
    public function getForRoutes()
    {
        $pages = $this->make(['translations'])
            ->withoutGlobalScopes()
            ->where('module', '!=', '')
            ->get()
            ->all();

        return $pages;
    }

    /**
     * Get sort data.
     *
     * @param int   $position
     * @param array $item
     *
     * @return array
     */
    protected function getSortData($position, $item)
    {
        return [
            'position'  => $position,
            'parent_id' => $item['parent_id'],
        ];
    }

    /**
     * Get all translated pages for a select/options.
     *
     * @return array
     */
    public function allForSelect()
    {
        $pages = $this->all([], true)
            ->nest()
            ->listsFlattened('system_name');

        return ['' => ''] + $pages;
    }

    /**
     * Fire event to reset children’s uri
     * Only applicable on nestable collections.
     *
     * @param Page $page
     *
     * @return void|null
     */
    protected function fireResetChildrenUriEvent($page)
    {
        event('page.resetChildrenUri', [$page]);
    }

    /**
     * Get all translated pages for a select/options.
     *
     * @return array
     */
    public function allForTreeMap()
    {
        $pages = $this->model->withoutGlobalScopes()->select(['pages.id', 'pages.parent_id'])->get()
            ->listsFlattened('parent_id');

        return $pages;
    }
}
