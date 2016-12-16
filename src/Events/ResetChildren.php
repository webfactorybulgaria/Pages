<?php

namespace TypiCMS\Modules\Pages\Events;

use Illuminate\Events\Dispatcher;
use TypiCMS\Modules\Pages\Shells\Models\Page;

class ResetChildren
{
    /**
     * Recursive method for emptying children’s uri
     * UriObserver will rebuild uris.
     *
     * @param Page $page
     *
     * @return void
     */
    public function resetChildrenUri(Page $page, $langsChanged = [])
    {
        foreach ($page->childrenWithTranslationsPageParent as $childPage) {
            foreach ($childPage->translations as $k => $translation) {
                if (empty($langsChanged) || !empty($langsChanged[$translation->locale])){
                    if (is_null($translation->uri)) {
                        $translation->uri = null;
                    } else {
                        $translation->uri = '';
                    }
                    $translation->timestamps = false; // disable update timestamps for performance
                }
            }
            $childPage->skipHistoryWrite = true;
            $childPage->save();
            $this->resetChildrenUri($childPage, $langsChanged);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     *
     * @return array
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen('page.resetChildrenUri', 'TypiCMS\Modules\Pages\Shells\Events\ResetChildren@resetChildrenUri');
    }
}
