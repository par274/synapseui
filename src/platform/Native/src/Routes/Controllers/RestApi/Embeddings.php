<?php

namespace NativePlatform\Routes\Controllers\RestApi;

use NativePlatform\Routes\Controller;
use NativePlatform\Routes\Controllers\Traits\{
    ServiceAccessors,
    RestApiHelper
};

class Embeddings extends Controller
{
    use ServiceAccessors, RestApiHelper;

    public function embeddings()
    {
        if ($this->request()->isMethod('POST'))
        {
            $body = json_decode($this->request()->getContent(), true);
            $this->validateBody($body, 'embed');

            $output = $this->getLLMAdapter()->embed([
                'model' => $body['model'],
                'input' => $body['input']
            ])->embeddings();

            return $this->renderer()->finalRender('json', $output);
        }
    }
}
