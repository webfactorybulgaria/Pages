<?php

namespace TypiCMS\Modules\Pages\Presenters;

use TypiCMS\Modules\Core\Shells\Presenters\Presenter;

class ModulePresenter extends Presenter
{
    /**
     * Get Uri without last segment.
     *
     * @param string $lang
     *
     * @return string URI without last segment
     */
    public function parentUri($lang)
    {
        $parentUri = '/';
        if ($this->entity->hasTranslation($lang)) {
            $parentUri = $this->entity->translate($lang)->uri;
        }
        $parentUri = explode('/', $parentUri);
        array_pop($parentUri);
        $parentUri = implode('/', $parentUri).'/';

        return $parentUri;
    }
}
