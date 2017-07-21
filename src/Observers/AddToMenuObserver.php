<?php

namespace TypiCMS\Modules\Pages\Observers;

use Illuminate\Support\Facades\Request;
use TypiCMS\Modules\Menus\Shells\Models\Menulink;
use TypiCMS\Modules\Pages\Shells\Models\Page;

class AddToMenuObserver
{
    /**
     * If a new homepage is defined, cancel previous homepage.
     *
     * @param Model $model eloquent
     *
     * @return void
     */
    public function created(Page $model)
    {
        if ($menu_id = Request::input('add_to_menu')) {
            $position = $this->getPositionFormMenu($menu_id);
            $data = [
                'menu_id'  => $menu_id,
                'page_id'  => $model->id,
                'position' => $position,
            ];
            foreach ($model->translations as $translation) {
                $data[$translation->locale]['title'] = $translation->title;
                $data[$translation->locale]['status'] = 1;
            }
            app('TypiCMS\Modules\Menus\Shells\Repositories\MenulinkInterface')->create($data);
        }
    }

    private function getPositionFormMenu($id)
    {
        $position = Menulink::where('menu_id', $id)->max('position');

        return $position + 1;
    }
}
