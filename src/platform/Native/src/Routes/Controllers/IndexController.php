<?php

namespace NativePlatform\Routes\Controllers;

use NativePlatform\Routes\Controller;
use NativePlatform\Routes\Controllers\Traits\ServiceAccessors;

class IndexController extends Controller
{
    use ServiceAccessors;

    public function index()
    {
        if ($this->request()->isMethod('GET'))
        {
            $translator = $this->translator();
            $template = $this->templater()->renderFromFile('index.tplx', [
                'app' => [
                    'config' => $this->config(),
                    'translator' => $translator,
                    'jsTranslatorPhraseList' => json_encode($this->getJsTranslationList($translator))
                ]
            ]);

            return $this->renderer()->finalRender('html', $template);
        }
        else if ($this->request()->isMethod('POST'))
        {
        }
    }
}
