<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;








class PageSizer extends Widget
{
    


    public $pageSizes = [5 => 5, 10 => 10, 20 => 20, 50 => 50];

    




    public function run()
    {
        $size = 20;
        $saved = Yii::$app->session->get('per-page');
        if (in_array($saved, $this->pageSizes)) {
            $size = $saved;
        }
        $selected = Yii::$app->request->get('per-page');
        if (in_array($selected, $this->pageSizes)) {
            $size = $selected;
        }

        Yii::$app->session->set('per-page', $size);

        return Html::tag('div', Html::tag('div',
            Html::label(Yii::t('podium/view', 'Results per page'), 'per-page')
            . ' '
            . Html::dropDownList('per-page', $size, $this->pageSizes, ['class' => 'form-control input-sm', 'id' => 'per-page']),
            ['class' => 'form-group']
        ), ['class' => 'pull-right form-inline']) . '<br><br>';
    }
}
