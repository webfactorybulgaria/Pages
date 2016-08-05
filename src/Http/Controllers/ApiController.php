<?php

namespace TypiCMS\Modules\Pages\Http\Controllers;

use Illuminate\Support\Facades\Request;
use TypiCMS\Modules\Core\Custom\Http\Controllers\BaseApiController;
use TypiCMS\Modules\Pages\Custom\Models\Page;
use TypiCMS\Modules\Pages\Custom\Repositories\PageInterface as Repository;

class ApiController extends BaseApiController
{
    public function __construct(Repository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * List resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($builder = null)
    {
        $models = $this->repository->allNested([], true);

        return response()->json($models, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $model = $this->repository->create(Request::all());
        $error = $model ? false : true;

        return response()->json([
            'error' => $error,
            'model' => $model,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  $model
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update()
    {
        $updated = $this->repository->update(Request::all());

        return response()->json([
            'error' => !$updated,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \TypiCMS\Modules\Pages\Custom\Models\Page $page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Page $page)
    {
        $deleted = $this->repository->delete($page);

        return response()->json([
            'error' => !$deleted,
        ]);
    }
}
