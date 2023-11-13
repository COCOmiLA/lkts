<?php

use common\components\ini\iniGet;
use common\models\settings\ParentDataSetting;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\grid\GridView;
use kartik\widgets\FileInput;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;








$appLanguage = Yii::$app->language;
$personalData = $questionary->personalData;

$emptyValue = Yii::t(
    'sandbox/view/questionary-block/all',
    'Текст для полей у которых пустое значение; на стр. просмотра заявления: `Не задано`'
);

$parent_setting = ParentDataSetting::findOne(['name' => 'hide_parent_passport_data_in_list']);
$hide_parent_passport_data = $parent_setting ? $parent_setting->value : false;
?>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/view/questionary-block/all',
                        'Заголовок блока анкеты; на стр. просмотра заявления: `Анкета`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4>
                                    <?= Yii::t(
                                        'sandbox/view/questionary-block/all',
                                        'Заголовок блока "Основные данные"; на стр. просмотра заявления: `Основные данные`'
                                    ); ?>
                                </h4>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-7 view-questionary-main-data">
                                        <div class="form-group required">
                                            <div class="row">
                                                <label class="col-5 col-form-label has-star">
                                                    <?= Yii::t(
                                                        'sandbox/view/questionary-block/all',
                                                        'Подпись поля "email" формы в блоке "Основные данные"; на стр. просмотра заявления: `Email`'
                                                    ) ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $questionary->user->email; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group required">
                                            <div class="row">
                                                <label class="col-5 col-form-label has-star">
                                                    <?= $personalData->getAttributeLabel('lastname'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $personalData->lastname; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group required">
                                            <div class="row">
                                                <label class="col-5 col-form-label has-star">
                                                    <?= $personalData->getAttributeLabel('firstname'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $personalData->firstname; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-5 col-form-label">
                                                    <?= $personalData->getAttributeLabel('middlename'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $personalData->middlename; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-5 col-form-label">
                                                    <?= $personalData->getAttributeLabel('gender_id'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext"><?= $personalData->relGender !== null ? $personalData->relGender->description : $emptyValue; ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-5 col-form-label">
                                                    <?= $personalData->getAttributeLabel('snils'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext"><?= ($personalData->snils ?? $emptyValue); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-5 col-form-label">
                                                    <?= $personalData->getAttributeLabel('country_id'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $personalData->citizenship ? $personalData->citizenship->name : $emptyValue; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group <?php echo QuestionarySettings::getSettingByName('require_birth_place_abiturient') ? 'required' : '' ?>">
                                            <div class="row">
                                                <label class="col-5 col-form-label <?php echo QuestionarySettings::getSettingByName('require_birth_place_abiturient') ? 'has-star' : '' ?>">
                                                    <?= $personalData->getAttributeLabel('birth_place'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $personalData->birth_place; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group required">
                                            <div class="row">
                                                <label class="col-5 col-form-label has-star">
                                                    <?= $personalData->getAttributeLabel('birthdate'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $personalData->birthdate; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-5 col-form-label">
                                                    <?= $personalData->getAttributeLabel('language_id'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?php $language = $personalData->language; ?>
                                                        <?= $language != null ? $language->description : $emptyValue ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group required">
                                            <div class="row">
                                                <label class="col-5 col-form-label has-star">
                                                    <?= $personalData->getAttributeLabel('main_phone'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $personalData->main_phone; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-5 col-form-label">
                                                    <?= $personalData->getAttributeLabel('secondary_phone'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <p class="form-control-plaintext">
                                                        <?= $personalData->secondary_phone; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-5 col-form-label">
                                                    <?= $personalData->getAttributeLabel('entrant_unique_code'); ?>
                                                </label>

                                                <div class="col-7">
                                                    <?php if (empty($personalData->entrant_unique_code)) : ?>
                                                        <?= Yii::t(
                                                            'sandbox/view/questionary-block/all',
                                                            'Информационное сообщение для пустого значения поля "entrant_unique_code" в блоке "Основные данные"; на стр. просмотра заявления: `Это поле будет заполнено после первой подачи заявления`'
                                                        ); ?>
                                                    <?php else : ?>
                                                        <?= Html::encode($personalData->entrant_unique_code); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($personalData->entrant_unique_code_special_quota)) : ?>
                                            <div class="form-group">
                                                <div class="row">
                                                    <label class="col-5 col-form-label">
                                                        <?= $personalData->getAttributeLabel('entrant_unique_code_special_quota'); ?>
                                                    </label>

                                                    <div class="col-7">
                                                        <?= Html::encode($personalData->entrant_unique_code_special_quota); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-5">
                                        <div class="form-group required">
                                            <div class="moderate-avatar">
                                                <label class="has-star">
                                                    <?= Yii::t(
                                                        'sandbox/view/questionary-block/all',
                                                        'Подпись поля "file" формы в блоке "Основные данные"; на стр. просмотра заявления: `Фото`'
                                                    ) ?>
                                                </label>

                                                <?php $abitAvatar = $questionary->getComputedAbiturientAvatar(); ?>
                                                <?= FileInput::widget([
                                                    'model' => $abitAvatar,
                                                    'attribute' => 'file',
                                                    'language' => $appLanguage,
                                                    'disabled' => true,
                                                    'options' => [
                                                        'multiple' => false,
                                                        'style' => 'height: 100%;'
                                                    ],
                                                    'pluginOptions' => [
                                                        'theme' => 'fa4',
                                                        'removeClass' => 'btn btn-danger',
                                                        'overwriteInitial' => true,
                                                        'initialPreviewAsData' => true,
                                                        'removeLabel' => Yii::t(
                                                            'sandbox/view/questionary-block/all',
                                                            'Подпись кнопки "Очистить файлы" виджета "Фото" в блоке "Основные данные"; на стр. просмотра заявления: `Очистить`'
                                                        ),
                                                        'removeFromPreviewOnError' => true,
                                                        'showCaption' => true,
                                                        'showUpload' => false,
                                                        'showRemove' => true,
                                                        'showClose' => false,
                                                        'initialPreview' => $abitAvatar->isNewRecord ? false : $abitAvatar->getFileDownloadUrl(),
                                                        'initialPreviewConfig' => $abitAvatar->isNewRecord ? false : [
                                                            [
                                                                'caption' => $abitAvatar->filename,
                                                                'type' => $abitAvatar->getExtension() === 'pdf' ? 'pdf' : 'image',
                                                                'fileId' => $abitAvatar->id,
                                                                'key' => $abitAvatar->id
                                                            ]
                                                        ],
                                                        'maxFileSize' => iniGet::getUploadMaxFilesize(),
                                                        'dropZoneEnabled' => true,
                                                    ],
                                                ]); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card mb-3 custom-kartik-table">
                            <div class="card-header">
                                <h4>
                                    <?= Yii::t(
                                        'sandbox/view/questionary-block/all',
                                        'Заголовок блока "Паспортные данные"; на стр. просмотра заявления: `Паспортные данные`'
                                    ); ?>
                                </h4>
                            </div>

                            <div class="card-body">
                                <?php $passports = new ActiveDataProvider([
                                    'query' => $questionary->getPassportData()
                                ]);
                                echo GridView::widget([
                                    'hover' => true,
                                    'headerContainer' => ['class' => 'thead-light'],
                                    'tableOptions' => ['class' => 'table-sm'],
                                    'striped' => false,
                                    'summary' => false,
                                    'pager' => [
                                        'firstPageLabel' => '<<',
                                        'prevPageLabel' => '<',
                                        'nextPageLabel' => '>',
                                        'lastPageLabel' => '>>',
                                    ],
                                    'dataProvider' => $passports,
                                    'layout' => '{items}{pager}',
                                    'floatHeader' => true,
                                    'resizableColumns' => false,
                                    'responsiveWrap' => false,
                                    'responsive' => true,
                                    'floatOverflowContainer' => true,
                                    'beforeHeader' => [
                                        [
                                            'columns' => [
                                                [
                                                    'content' => Yii::t(
                                                        'sandbox/view/questionary-block/all',
                                                        'Заголовок таблицы паспортов; на стр. просмотра заявления: `Реквизиты документа`'
                                                    ),
                                                    'options' => [
                                                        'colspan' => 7,
                                                        'class' => 'text-center'
                                                    ]
                                                ],
                                            ],
                                            'options' => ['class' => 'skip-export']
                                        ]
                                    ],
                                    'columns' => [
                                        'series',
                                        'number',
                                        [
                                            'value' => 'documentTypeDescription',
                                            'label' => Yii::t(
                                                'sandbox/view/questionary-block/all',
                                                'Подпись поля "documentTypeDescription" таблицы; на стр. просмотра заявления: `Тип документа`'
                                            ),
                                        ],
                                        [
                                            'attribute' => 'departmentCode'                                    
                                        ],
                                        [
                                            'attribute' => 'issuedBy'                                      
                                        ],
                                        'issued_date',
                                    ]
                                ]); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4>
                                    <?= Yii::t(
                                        'sandbox/view/questionary-block/all',
                                        'Заголовок блока "Адрес"; на стр. просмотра заявления: `Адрес постоянной регистрации`'
                                    ); ?>
                                </h4>
                            </div>

                            <div class="card-body">
                                <?= $this->render(
                                    '_address_data',
                                    ['addressData' => $questionary->addressData ?? (new AddressData)]
                                ); ?>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($application) && $application->type->can_see_actual_address) : ?>
                        <div class="col-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4>
                                        <?= Yii::t(
                                            'sandbox/view/questionary-block/all',
                                            'Заголовок блока "Адрес"; на стр. просмотра заявления: `Адрес проживания`'
                                        ); ?>
                                    </h4>
                                </div>

                                <div class="card-body">
                                    <?= $this->render(
                                        '_address_data',
                                        ['addressData' => $questionary->getOrCreateActualAddressData()]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-12">
                        <div class="card mb-3 custom-kartik-table">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-sm-10 col-12">
                                        <h4>
                                            <?= Yii::t(
                                                'sandbox/view/questionary-block/all',
                                                'Заголовок блока "Родители"; на стр. просмотра заявления: `Данные родителей или законных представителей {message}`',
                                                ['message' => TooltipWidget::widget(['message' => Yii::$app->configurationManager->getText('parents_tooltip')])]
                                            ); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <?php $parents = new ActiveDataProvider([
                                    'query' => $questionary->getParentData()
                                ]); ?>
                                <?= GridView::widget([
                                    'hover' => true,
                                    'headerContainer' => ['class' => 'thead-light'],
                                    'tableOptions' => ['class' => 'table-sm'],
                                    'striped' => false,
                                    'summary' => false,
                                    'pager' => [
                                        'firstPageLabel' => '<<',
                                        'prevPageLabel' => '<',
                                        'nextPageLabel' => '>',
                                        'lastPageLabel' => '>>',
                                    ],
                                    'dataProvider' => $parents,
                                    'layout' => '{items}{pager}',
                                    'floatHeader' => true,
                                    'resizableColumns' => false,
                                    'responsiveWrap' => false,
                                    'responsive' => true,
                                    'floatOverflowContainer' => true,
                                    'columns' => [
                                        [
                                            'label' => Yii::t(
                                                'sandbox/view/questionary-block/all',
                                                'Подпись поля "type.name" таблицы; на стр. просмотра заявления: `Степень родства`'
                                            ),
                                            'value' => 'type.name',
                                        ],
                                        [
                                            'label' => Yii::t(
                                                'sandbox/view/questionary-block/all',
                                                'Подпись поля "personalData.absFullName" таблицы; на стр. просмотра заявления: `ФИО`'
                                            ),
                                            'value' => 'personalData.absFullName'
                                        ],
                                        [
                                            'label' => Yii::t(
                                                'sandbox/view/questionary-block/all',
                                                'Подпись поля "personalData.main_phone" таблицы; на стр. просмотра заявления: `Телефон`'
                                            ),
                                            'value' => 'personalData.main_phone'
                                        ],
                                        [
                                            'label' => Yii::t(
                                                'sandbox/view/questionary-block/all',
                                                'Подпись поля "passportData.series" таблицы; на стр. просмотра заявления: `Серия паспорта`'
                                            ),
                                            'value' => 'passportData.series',
                                            'visible' => !$hide_parent_passport_data
                                        ],
                                        [
                                            'label' => Yii::t(
                                                'sandbox/view/questionary-block/all',
                                                'Подпись поля "passportData.number" таблицы; на стр. просмотра заявления: `Номер паспорта`'
                                            ),
                                            'value' => 'passportData.number',
                                            'visible' => !$hide_parent_passport_data
                                        ],
                                    ]
                                ]); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <?= Yii::t(
                                        'sandbox/view/questionary-block/all',
                                        'Заголовок блока "Прочее"; на стр. просмотра заявления: `Прочее`'
                                    ); ?>
                                </h4>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-6">
                                        <div class="row">
                                            <label class="col-6 col-form-label">
                                                <?= $personalData->getAttributeLabel('need_dormitory') ?>
                                            </label>

                                            <div class="col-6">
                                                <?= Html::activeCheckbox(
                                                    $personalData,
                                                    'need_dormitory',
                                                    ['label' => false, 'class' => 'form-checkbox', 'readOnly' => true, 'disabled' => 'disabled']
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>