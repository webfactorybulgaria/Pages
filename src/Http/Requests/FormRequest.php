<?php

namespace TypiCMS\Modules\Pages\Http\Requests;

use TypiCMS\Modules\Core\Shells\Http\Requests\AbstractFormRequest;

class FormRequest extends AbstractFormRequest
{
    public function rules()
    {
        return [
            'template'           => 'alpha_dash|max:255',
            'system_name'        => 'required',
            '*.slug'             => 'alpha_dash|max:255',
            '*.title'            => 'max:255',
            '*.meta_keywords'    => 'max:255',
            '*.meta_description' => 'max:255',
        ];
    }
}
