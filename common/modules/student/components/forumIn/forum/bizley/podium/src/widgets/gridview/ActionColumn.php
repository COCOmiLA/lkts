<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\gridview;

use Yii;
use yii\grid\ActionColumn as YiiActionColumn;
use yii\helpers\Html;







class ActionColumn extends YiiActionColumn
{
    


    public $headerOptions = ['class' => 'text-right'];
    







    public $contentOptions = ['class' => 'text-right'];
    


    public $buttonOptions = [
        'class' => 'btn btn-default btn-xs',
        'data-pjax' => '0',
        'data-toggle' => 'tooltip',
        'data-placement' => 'top',
    ];

    


    public function init()
    {
        parent::init();
        $this->header = Yii::t('podium/view', 'Actions');
        $this->grid->view->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
    }

    




    public static function buttonOptions($options)
    {
        return array_merge(
            [
                'class' => 'btn btn-default btn-xs',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
            ],
            $options
        );
    }

    




    public static function mutedButton($icon)
    {
        return Html::a(Html::tag('span', '', ['class' => $icon]), '#', ['class' => 'btn btn-xs disabled text-muted']);
    }
}
