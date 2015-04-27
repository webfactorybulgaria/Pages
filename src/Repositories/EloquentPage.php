<?php
namespace TypiCMS\Modules\Pages\Repositories;

use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Input;
use Log;
use TypiCMS\Repositories\RepositoriesAbstract;

class EloquentPage extends RepositoriesAbstract implements PageInterface
{

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Update an existing model
     *
     * @param array  Data needed for model update
     * @return boolean
     */
    public function update(array $data)
    {
        $model = $this->model->find($data['id']);

        $model->fill($data);

        $this->syncRelation($model, $data, 'galleries');

        if ($model->save()) {
            event('page.resetChildrenUri', [$model]);
            return true;
        }

        return false;
    }

    /**
     * Get a page by its uri
     *
     * @param  string                      $uri
     * @param  string                      $locale
     * @return TypiCMS\Modules\Models\Page $model
     */
    public function getFirstByUri($uri, $locale)
    {
        $model = $this->make(['translations'])
            ->whereHas('translations', function (Builder $query) use ($uri, $locale) {
                $query->where('uri', $uri)
                    ->where('locale', $locale);
                if (! Input::get('preview')) {
                    $query->where('status', 1);
                }
            })
            ->firstOrFail();
        return $model;
    }

    /**
     * Get submenu for a page
     *
     * @return Collection
     */
    public function getSubMenu($uri, $all = false)
    {
        $rootUriArray = explode('/', $uri);
        $uri = $rootUriArray[0];
        if (in_array($uri, config('translatable.locales'))) {
            if (isset($rootUriArray[1])) { // i
                $uri .= '/' . $rootUriArray[1]; // add next part of uri in locale
            }
        }

        $query = $this->model
            ->with('translations')
            ->select('*')
            ->addSelect('pages.id AS id')
            ->join('page_translations', 'pages.id', '=', 'page_translations.page_id')
            ->where('uri', '!=', $uri)
            ->where('uri', 'LIKE', $uri.'%');

        if (! $all) {
            $query->where('status', 1);
        }
        $query->where('locale', config('app.locale'));

        $models = $query->order()->get()->nest();

        return $models;
    }

    /**
     * Get pages linked to module to build routes
     *
     * @return array
     */
    public function getForRoutes()
    {
        $routes = [];

        try {
            $pages = $this->make(['translations'])
                ->online()
                ->where('module', '!=', '')
                ->get();

            foreach ($pages as $page) {
                $routes[$page->module] = $page;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        return $routes;
    }

    /**
     * Get all uris
     *
     * @return array
     */
    public function allUris()
    {
        return DB::table('page_translations')->lists('uri', 'id');
    }

    /**
     * Get sort data
     *
     * @param  integer $position
     * @param  array   $item
     * @return array
     */
    protected function getSortData($position, $item)
    {
        return [
            'position' => $position,
            'parent_id' => $item['parent_id']
        ];
    }

    /**
     * Fire event to reset children’s uri
     * Only applicable on nestable collections
     *
     * @param  Page    $page
     * @return void|null
     */
    protected function fireResetChildrenUriEvent($page)
    {
        event('page.resetChildrenUri', [$page]);
    }
}
