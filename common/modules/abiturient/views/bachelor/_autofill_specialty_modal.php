<?php

use common\models\dictionary\Speciality;
use common\modules\abiturient\assets\AutofillSpecialty\AutofillSpecialtyAsset;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;







AutofillSpecialtyAsset::register($this);

$specialitiesForAutofill = $bachelorApplication
    ->getSpecialitiesForAutofillQuery()
    ->all();

$this->registerJsVar(
    'autofill_specialty_url',
    Url::to(['/bachelor/autofill-specialty', 'application_id' => $bachelorApplication->id])
);

$renderedSpec = [];
$displayCode = ArrayHelper::getValue($bachelorApplication, 'type.display_code');
$displayGroupName = ArrayHelper::getValue($bachelorApplication, 'type.display_group_name');
$displaySpecialityName = ArrayHelper::getValue($bachelorApplication, 'type.display_speciality_name');
if (!isset($modelIdentification)) {
    $modelIdentification = rand(0, 1000);
}

?>

<?php Modal::begin([
    'title' => Html::tag(
        'h4',
        Yii::t(
            'abiturient/header/autofill-specialty-modal',
            'Заголовок модального окна подтверждения отправки заявления; на панели навигации ЛК: `Поступление на общих основаниях`'
        )
    ),
    'id' => 'autofill_specialty-confirm-modal',
    'size' => 'modal-lg',
    'toggleButton' => false,
]); ?>

    <div class="row no-loading-elements">
        <div class="col-12">
            <div id="place_for_alert"></div>
        </div>
    </div>

    <div class="row loading-elements">
        <div class="col-12">
            <div class="loader"></div>
        </div>
    </div>

    <div class="row no-loading-elements">
        <div class="col-12">
            <?php echo Yii::$app->configurationManager->getText('text_in_popup_autofill_specialty_on_a_universal_basis'); ?>
        </div>
    </div>

    <div class="row no-loading-elements" id="posable-speciality">
        <div class=" col-12 speciality-container pre-scrollable">
            <?php foreach ($specialitiesForAutofill as $availableSpecialty) : ?>
                <?php  ?>

                <?php
                if (in_array($availableSpecialty->id, $renderedSpec)) {
                    continue;
                }
                $renderedSpec[] = $availableSpecialty->id;
                ?>

                <?= $this->render(
                    'partials/application/_add_application_modal_panel',
                    compact([
                        'displayCode',
                        'displayGroupName',
                        'availableSpecialty',
                        'displaySpecialityName',
                        'modelIdentification',
                    ])
                ); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <hr class="face_footer">

    <div class="row">
        <div class="col-12">
            <?= Html::button(
                Yii::t(
                    'abiturient/header/autofill-specialty-modal',
                    'Подпись кнопки согласия на авто добавление КГ; на панели навигации ЛК: `Добавить`'
                ),
                [
                    'disabled' => true,
                    'id' => 'autofill_specialty-confirm-button',
                    'class' => 'btn btn-success float-right lef-btn-gap anti-clicker-btn',
                ]
            ) ?>

            <?= Html::button(
                Yii::t(
                    'abiturient/header/autofill-specialty-modal',
                    'Подпись кнопки отказа на авто добавление КГ; на панели навигации ЛК: `Нет, не нужно`'
                ),
                [
                    'id' => 'autofill_specialty-cancel-button',
                    'class' => 'btn btn-primary float-right anti-clicker-btn',
                ]
            ) ?>
        </div>
    </div>

<?php Modal::end();
