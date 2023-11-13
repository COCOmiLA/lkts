<?php

namespace common\modules\student\components\block;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class BlockWidget extends Widget
{
    public $list;
    public $units_error;

    public function init()
    {
        parent::init();

        $user = Yii::$app->user->identity;

        $blockLoader = Yii::$app->getModule('student')->blockLoader;
        $blockLoader->setParams($user->guid);

        list($this->list, $this->units_error) = $blockLoader->loadList();
    }

    public function run()
    {
        if (sizeof($this->list) > 0) {
            return $this->render('block_widget', [
                'list' => $this->list,
                'units_error' => $this->units_error,
            ]);
        } else {
            return implode("\n", [
                Html::beginTag('h4'),
                'Нет данных',
                Html::endTag('h4')
            ]) . "\n";
        }
    }
}
