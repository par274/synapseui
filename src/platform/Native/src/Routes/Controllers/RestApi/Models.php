<?php

namespace NativePlatform\Routes\Controllers\RestApi;

use NativePlatform\Routes\Controller;
use NativePlatform\Routes\Controllers\Traits\{
    ServiceAccessors,
    RestApiHelper
};

class Models extends Controller
{
    use ServiceAccessors, RestApiHelper;

    public function models()
    {
        if ($this->request()->isMethod('GET'))
        {
            $models = $this->getLLMAdapter()->listModels()->data();

            if (empty($models['data']))
            {
                return $this->renderer()->finalRender(
                    'json',
                    $this->responseError("No models available", 'invalid_request_error', 'model', 'no_models'),
                    404
                );
            }

            return $this->renderer()->finalRender(
                'json',
                $models,
                200
            );
        }
    }

    public function info()
    {
        $params = $this->params();

        if ($this->request()->isMethod('GET'))
        {
            $model = $this->getLLMAdapter()->listModels()->findModel($params['model']);

            if ($model === null)
            {
                return $this->renderer()->finalRender(
                    'json',
                    $this->responseError(
                        "The model '{$params['model']}' does not exist",
                        'invalid_request_error',
                        'model',
                        'model_not_found'
                    ),
                    404
                );
            }

            return $this->renderer()->finalRender(
                'json',
                $model,
                200
            );
        }
    }
}
