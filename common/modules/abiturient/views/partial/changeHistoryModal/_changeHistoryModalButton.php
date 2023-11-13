<?php

use common\models\settings\ChangeHistorySettings;
use common\modules\abiturient\assets\changeHistoryAsset\ChangeHistoryAsset;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\helpers\Url;
use yii\web\View;







?>

<?php if (!isset($button)) : ?>
    <button type="button" id="changeHistoryModalButton" data-toggle="modal" data-target="#changeHistoryModal" class="btn btn-primary">
        <?= Yii::t(
            'abiturient/change-history',
            'Подпись кнопки в модальном окне истории изменений: `История изменений`'
        ) ?>

        <i class="fa fa-list-alt" style="margin-left: 20px" aria-hidden="true"></i>
    </button>
<?php else : ?>
    <?= $button; ?>
<?php endif; ?>

<?php

$this->registerJsVar('pjaxUrl', Url::toRoute(['bachelor/application-change-history', 'id' => $application->id]), View::POS_END);

$this->registerJsVar('infiniteScrollUrl', Url::toRoute(['bachelor/application-infinite-scroll-history', 'id' => $application->id]), View::POS_END);
$this->registerJsVar('infiniteScrollLimit', ChangeHistorySettings::getValueByName('following_load_limit'), View::POS_END);
$this->registerJsVar('infiniteScrollOffset', ChangeHistorySettings::getValueByName('first_load_limit'), View::POS_END);
$this->registerJsVar('infiniteScrollInProcess', false, View::POS_END);
ChangeHistoryAsset::register($this);
