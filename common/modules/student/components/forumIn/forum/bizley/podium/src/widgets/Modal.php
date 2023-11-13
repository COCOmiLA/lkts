<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets;

use Yii;
use yii\bootstrap\Modal as YiiModal;
use yii\helpers\Html;







class Modal extends YiiModal
{
    


    public $options = ['aria-hidden' => 'true'];
    


    public $footerConfirmOptions = [];
    


    public $footerConfirmUrl = '#';


    


    public function init()
    {
        $this->header = Html::tag('h4', $this->header, ['class' => 'modal-title', 'id' => $this->id . 'Label']);
        $this->options['aria-labelledby'] = $this->id . 'Label';
        $this->footer = Html::button(Yii::t('podium/view', 'Cancel'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal'])
                . "\n" . Html::a($this->footer, $this->footerConfirmUrl, $this->footerConfirmOptions);

        parent::init();
    }
}
