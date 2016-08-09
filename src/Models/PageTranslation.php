<?php

namespace TypiCMS\Modules\Pages\Models;

use TypiCMS\Modules\Core\Shells\Models\BaseTranslation;

class PageTranslation extends BaseTranslation
{
    /**
     * get the parent model.
     */
    public function page()
    {
        return $this->belongsTo('TypiCMS\Modules\Pages\Shells\Models\Page');
    }

    public function owner()
    {
        return $this->belongsTo('TypiCMS\Modules\Pages\Shells\Models\Page', 'page_id')->withoutGlobalScopes();
    }
}
