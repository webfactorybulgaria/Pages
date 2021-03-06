<?php

namespace TypiCMS\Modules\Pages\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use TypiCMS;
use TypiCMS\Modules\Core\Shells\Http\Controllers\BasePublicController;
use TypiCMS\Modules\Pages\Shells\Repositories\PageInterface;

class PublicController extends BasePublicController
{
    public function __construct(PageInterface $page)
    {
        parent::__construct($page, 'page');
    }

    /**
     * Page uri : lang/slug.
     *
     * @return \Illuminate\Http\Response | \Illuminate\Http\RedirectResponse
     */
    public function uri($page = null)
    {
        $app = app();
        if ($app->make('request')->path() != $page->uri() && !$page->is_home) {
            return redirect($page->uri());
        }

        $app->instance('currentPage', $page);

        if (!$page) {
            abort('404');
        }

        if ($page->private && !Auth::check()) {
            return redirect()->guest(route(config('app.locale') . '.login'));
        }

        if ($page->redirect) {
            $childUri = $page->children->first()->uri();

            return redirect($childUri);
        }

        // get submenu
        $children = $this->repository->getSubMenu($page->uri);

        $templateDir = 'pages::'.config('typicms.template_dir', 'public').'.';
        $template = $page->template ?: 'default';

        if (!view()->exists($templateDir.$template)) {
            info('Template '.$template.' not found, switching to default template.');
            $template = 'default';
        }

        return response()->view($templateDir.$template, compact('children', 'page'));
    }

    /**
     * Get browser language or default locale and redirect to homepage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToHomepage()
    {
        $homepage = $this->repository->getFirstBy('is_home', 1);
        $locale = $this->getBrowserLanguageOrDefault();

        return redirect($homepage->uri($locale));
    }

    /**
     * Get browser language or app.locale.
     *
     * @return string
     */
    private function getBrowserLanguageOrDefault()
    {
        if (config('app.detect_locale') && $browserLanguage = getenv('HTTP_ACCEPT_LANGUAGE')) {
            $browserLocale = substr($browserLanguage, 0, 2);
            if (in_array($browserLocale, TypiCMS::getOnlineLocales())) {
                return $browserLocale;
            }
        }

        return config('app.locale');
    }

    /**
     * Display the lang chooser.
     *
     * @return void
     */
    public function langChooser()
    {
        $homepage = $this->repository->getFirstBy('is_home', 1);
        $locales = TypiCMS::getOnlineLocales();

        return view('core::public.lang-chooser')
            ->with(compact('homepage', 'locales'));
    }
}
