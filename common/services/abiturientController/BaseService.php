<?php

namespace common\services\abiturientController;

use Closure;
use common\components\configurationManager;
use common\models\EmptyCheck;
use yii\web\Request;





class BaseService
{
    
    protected Request $request;

    
    protected configurationManager $configurationManager;

    



    public function __construct(
        Request $request,
        configurationManager $configurationManager
    ) {
        $this->request = $request;
        $this->configurationManager = $configurationManager;
    }

    





    protected function makeDataFormattedForDepDrop(Closure $callback, array $items): array
    {
        return array_values(array_filter(
            array_map(
                $callback,
                $items
            ),
            function ($item) {
                return isset($item['id']) && !EmptyCheck::isEmpty($item['id']);
            }
        ));
    }
}
