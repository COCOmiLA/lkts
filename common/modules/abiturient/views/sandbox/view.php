<?php

use common\components\AccountingBenefits\assets\AccountingBenefitsComponentAsset;
use common\components\attachmentWidget\AttachmentWidget;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\ToAssocCaster;
use common\modules\abiturient\assets\sandboxViewAsset\SandboxViewAsset;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\helpers\UserInfoRenderHelper;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\PersonalData;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use kartik\form\ActiveForm;










SandboxViewAsset::register($this);
AccountingBenefitsComponentAsset::register($this);

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'sandbox/view/all',
    'Заголовок страницы просмотра анкеты поступающего: `Просмотр заявлений`'
);

$divider = '12';

$personalData = $questionary->personalData;
$abiturientGenderRef = ArrayHelper::getValue($personalData, 'relGender.ref_key');

$template = '{input}\n{error}';

?>

<div class="row form-group">
    <?php if (
        Yii::$app->user->identity->isModer() &&
        isset($moderate_app_id) &&
        !$application->isArchive() &&
        $application->draft_status != IDraftable::DRAFT_STATUS_APPROVED
    ) : ?>
        <div class="col-6">
            <?php echo Html::a(
                Yii::t(
                    'sandbox/view/all',
                    'Подпись кнопки возвращающей к проверке заявления; на стр. просмотра заявления: `Вернуться к проверке заявления`'
                ),
                Url::to(['/sandbox/moderate', 'id' => $moderate_app_id]),
                ['class' => 'btn btn-success']
            );
            $divider = '6'; ?>
        </div>
    <?php endif; ?>

    <div class="col-<?php echo $divider ?>">
        <?php $url = Url::toRoute(['sandbox/index']);
        if (Yii::$app->user->identity->isViewer()) {
            $url = Url::toRoute(['viewer/index']);
        } ?>
        <a href="<?= $url; ?>" class="btn btn-primary pull-right">
            <?= Yii::t(
                'sandbox/view/all',
                'Подпись кнопки возвращающей к списку с заявлениями; на стр. просмотра заявления: `Назад к списку заявлений поступающих`'
            ) ?>
        </a>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <?php echo Html::a(
            Yii::t(
                'sandbox/view/all',
                'Подпись кнопки возвращающей к проверке заявления; на стр. просмотра заявления: `Перечень ключевых изменений заявления`'
            ),
            Url::to(['/sandbox/view-archive-application', 'user_id' => $application->user_id, 'id' => $application->id]),
            ['class' => 'btn btn-info float-right']
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h3>
            <?php switch ($abiturientGenderRef) {
                case PersonalData::getGenderMale():
                    echo Yii::t(
                        'sandbox/view/all',
                        'Подпись с ФИО поступающего подавшего заявления для случая если поступающий мужского пола; на стр. проверки анкеты поступающего: `Подал заявление {fio}`',
                        ['fio' => $application->fio]
                    );
                    break;

                case PersonalData::getGenderFemale():
                    echo Yii::t(
                        'sandbox/view/all',
                        'Подпись с ФИО поступающего подавшего заявления для случая если поступающий женского пола; на стр. проверки анкеты поступающего: `Подала заявление {fio}`',
                        ['fio' => $application->fio]
                    );
                    break;

                default:
                    echo Yii::t(
                        'sandbox/view/all',
                        'Подпись с ФИО поступающего подавшего заявления для случая если не удалось определить пол поступающего; на стр. проверки анкеты поступающего: `Подал(а) заявление {fio}`',
                        ['fio' => $application->fio]
                    );
                    break;
            } ?>
        </h3>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="change-button-wrapper" style="margin-bottom: 20px; text-align: right;">
            <?= $this->render(
                '@abiturient/views/partial/changeHistoryModal/_changeHistoryModalButton',
                ['application' => $application]
            ); ?>
        </div>

        <?= $this->render('@abiturient/views/partial/changeHistoryModal/_changeHistoryModal'); ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php $appApprovingError = Yii::$app->session->getFlash('appApprovingError', null, true); ?>
        <?php if ($appApprovingError) : ?>
            <?= $this->render(
                './partial/_application_step_status',
                ['stepsInfo' => $appApprovingError,]
            ); ?>
        <?php endif; ?>

        <?php if (isset($relationInfo) && $relationInfo) : ?>
            <?php if (isset($relationInfo['abit']) && $relationInfo['abit']) : ?>
                <?php if ($code_message) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $code_message; ?>
                    </div>
                <?php endif; ?>

                <div class="card mb-3">
                    <div class="card-header">
                        <h4>
                            <?= Yii::t(
                                'sandbox/view/comparison-applicant',
                                'Заголовок блока "Сопоставление поступающего" на стр. просмотра заявления: `Сопоставление поступающего`'
                            ) ?>
                        </h4>
                    </div>

                    <div class="card-body">
                        <?php $form = ActiveForm::begin(['action' => ['sandbox/bind'], 'options' => ['method' => 'post']]); ?>
                        <?php foreach ($relationInfo['abit'] as $infos) : ?>
                            <?php
                            $assoc_infos = ToAssocCaster::getAssoc($infos);
                            
                            $current_user_ref = ReferenceTypeManager::GetOrCreateReference(StoredUserReferenceType::class, $assoc_infos['EntrantRef']);
                            if (!$current_user_ref) {
                                continue;
                            }
                            $current_code = $current_user_ref->reference_id;
                            ?>
                            <div class="alert alert-info" role="alert">
                                <p>
                                    <?= Yii::t(
                                        'sandbox/view/comparison-applicant',
                                        'Текст сообщения, что обнаружены дубли ФЛ; блока "Сопоставление поступающего" на стр. просмотра заявления: `Обнаружены совпадения ФИО и даты рождения.`'
                                    ) ?>
                                </p>

                                <p>
                                    <label>

                                        <?= Html::radio(
                                            'user_ref_id',
                                            false,
                                            ['value' => $current_user_ref->id, 'required' => true]
                                        ); ?>
                                        <?= Yii::t(
                                            'sandbox/view/comparison-applicant',
                                            'Перечисление дублирующейся информации; блока "Сопоставление поступающего" на стр. просмотра заявления: `Связать анкету с физ. лицом <b>{currentCode} - {associativeInfos}</b>`',
                                            [
                                                'currentCode' => $current_code,
                                                'associativeInfos' => UserInfoRenderHelper::getUserDescription($assoc_infos, $current_user_ref),
                                            ]
                                        ) ?>
                                    </label>
                                </p>
                            </div>

                            <?= Html::hiddenInput('application_id', $application->id); ?>
                        <?php endforeach; ?>

                        <?= Html::submitButton(
                            Yii::t(
                                'sandbox/view/comparison-applicant',
                                'Подпись кнопки сопоставления; блока "Сопоставление поступающего" на стр. просмотра заявления: `Сопоставить поступающего`'
                            ),
                            ['class' => 'btn btn-success']
                        ); ?>

                        <?= Html::a(
                            Yii::t(
                                'sandbox/view/comparison-applicant',
                                'Подпись кнопки отмены сопоставления, что обнаружены дубли ФЛ; блока "Сопоставление поступающего" на стр. просмотра заявления: `Вернуться к проверке заявления (отменить сопоставление)`'
                            ),
                            Url::to(['sandbox/moderate', 'id' => $id]),
                            [
                                'type' => 'button',
                                'class' => 'btn btn-primary',
                                'style' => 'margin-left: 15px;'
                            ]
                        ); ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($relationInfo['parents']) && $relationInfo['parents']) : ?>
                <?php if ($parents_code_message) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $parents_code_message; ?>
                    </div>
                <?php endif; ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>
                            <?= Yii::t(
                                'sandbox/view/parent-comparison',
                                'Заголовок блока "Сопоставление родителя" на стр. просмотра заявления: `Сопоставление родителя`'
                            ) ?>
                        </h4>
                    </div>

                    <div class="card-body">
                        <?php foreach ($relationInfo['parents'] as $parent_id => $parent_infos) : ?>
                            <?php $form = ActiveForm::begin(['action' => ['sandbox/bind-parent', 'id' => $application->id], 'options' => ['method' => 'post']]); ?>
                            <div class="alert alert-info" role="alert">
                                <p>
                                    <?= Yii::t(
                                        'sandbox/view/parent-comparison',
                                        'Текст сообщения, что обнаружены дубли ФЛ; блока "Сопоставление родителя" на стр. просмотра заявления: `Обнаружены совпадения ФИО и даты рождения родителя {fullName}`',
                                        ['fullName' => ArrayHelper::getValue(
                                            $questionary
                                                ->getParentData()
                                                ->andWhere(['id' => $parent_id])
                                                ->one(),
                                            'personalData.absFullName'
                                        )]
                                    ) ?>
                                </p>

                                <?php foreach ($parent_infos as $parent_possible_info) : ?>
                                    <?php
                                    $assoc_parent_possible_info = ToAssocCaster::getAssoc($parent_possible_info);
                                    
                                    $current_user_ref = ReferenceTypeManager::GetOrCreateReference(StoredUserReferenceType::class, $assoc_parent_possible_info['EntrantRef']);
                                    if (!$current_user_ref) {
                                        continue;
                                    }
                                    $current_code = $current_user_ref->reference_id;
                                    ?>
                                    <p>
                                        <label>
                                            <?= Html::radio(
                                                "parent[{$parent_id}][user_ref_id]",
                                                false,
                                                ['value' => $current_user_ref->id, 'required' => true]
                                            ); ?>

                                            <?= Yii::t(
                                                'sandbox/view/parent-comparison',
                                                'Перечисление дублирующейся информации; блока "Сопоставление родителя" на стр. просмотра заявления: `Связать анкету родителя с физ. лицом <b>{currentCode} - {associativeInfos}</b>`',
                                                [
                                                    'currentCode' => $current_code,
                                                    'associativeInfos' => UserInfoRenderHelper::getUserDescription($assoc_parent_possible_info, $current_user_ref),
                                                ]
                                            ) ?>
                                        </label>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                            <?= Html::hiddenInput('questionary_id', $questionary->id); ?>
                            <?= Html::hiddenInput('parent_id', $parent_id); ?>

                            <?= Html::submitButton(
                                Yii::t(
                                    'sandbox/view/parent-comparison',
                                    'Подпись кнопки сопоставления; блока "Сопоставление родителя" на стр. просмотра заявления: `Сопоставить родителя`'
                                ),
                                ['class' => 'btn btn-success']
                            ); ?>

                            <?= Html::a(
                                Yii::t(
                                    'sandbox/view/parent-comparison',
                                    'Подпись кнопки отмены сопоставления, что обнаружены дубли ФЛ; блока "Сопоставление родителя" на стр. просмотра заявления: `Вернуться к проверке заявления (отменить сопоставление)`'
                                ),
                                Url::to(['sandbox/moderate', 'id' => $id]),
                                ['class' => 'btn btn-primary', 'type' => 'button', 'style' => 'margin-left: 15px;']
                            ); ?>

                            <?php ActiveForm::end(); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->render(
    'partial/_questionary_view',
    [
        'questionary' => $questionary,
        'application' => $application
    ]
) ?>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/view/education',
                        'Заголовок панели с образованиями; на стр. просмотра заявления: `Сведения об образовании`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <?php $educations = $application->educations ?>
                <?php if (!empty($educations)) : ?>
                    <?php $dividerCounts = count($educations) - 1; ?>
                    <?php foreach ($educations as $education) : ?>
                        <div class="row">
                            <div class="form-group col-6 required">
                                <div class="row">
                                    <label class="col-4 control-label has-star">
                                        <?= $education->getAttributeLabel('education_type_id'); ?>
                                    </label>

                                    <div class="col-8">
                                        <p class="form-control-static">
                                            <?php if ($education != null && $education->educationType != null) {
                                                echo $education->educationType->description;
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-6">
                                <div class="row">
                                    <label class="col-5 control-label">
                                        <?= $education->getAttributeLabel('number'); ?>
                                    </label>

                                    <div class="col-7">
                                        <p class="form-control-static">
                                            <?php if ($education != null) {
                                                echo $education->number;
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-6 required">
                                <div class="row">
                                    <label class="col-4 control-label has-star">
                                        <?= $education->getAttributeLabel('education_level_id'); ?>
                                    </label>

                                    <div class="col-8">
                                        <p class="form-control-static">
                                            <?php if ($education) {
                                                echo ArrayHelper::getValue($education->educationLevel, 'reference_name', '');
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-6 required">
                                <div class="row">
                                    <label class="col-5 control-label has-star">
                                        <?= $education->getAttributeLabel('contractor_id'); ?>
                                    </label>

                                    <div class="col-7">
                                        <p class="form-control-static">
                                            <?php if ($education != null) {
                                                echo $education->schoolName;
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-6 required">
                                <div class="row">
                                    <label class="col-4 control-label has-star">
                                        <?= $education->getAttributeLabel('document_type_id'); ?>
                                    </label>

                                    <div class="col-8">
                                        <p class="form-control-static">
                                            <?php if ($education != null && $education->documentType != null) {
                                                echo $education->documentType->description;
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-6 required">
                                <div class="row">
                                    <label class="col-5 control-label has-star">
                                        <?= $education->getAttributeLabel('date_given'); ?>
                                    </label>
                                    <div class="col-7">
                                        <p class="form-control-static">
                                            <?php if ($education != null) {
                                                echo $education->date_given;
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-6 required">
                                <div class="row">
                                    <label class="col-4 control-label has-star">
                                        <?= $education->getAttributeLabel('profile_ref_id'); ?>
                                    </label>

                                    <div class="col-8">
                                        <p class="form-control-static">
                                            <?php if ($education != null) {
                                                echo $education->profileRefDescription;
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-6 required">
                                <div class="row">
                                    <label class="col-5 control-label has-star">
                                        <?= $education->getAttributeLabel('edu_end_year'); ?>
                                    </label>

                                    <div class="col-7">
                                        <p class="form-control-static">
                                            <?php if ($education != null) {
                                                echo $education->edu_end_year;
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-6">
                                <div class="row">
                                    <label class="col-4 control-label">
                                        <?= $education->getAttributeLabel('series'); ?>
                                    </label>

                                    <div class="col-8">
                                        <p class="form-control-static">
                                            <?php if ($education != null) {
                                                echo $education->series;
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-6 required">
                                <div class="row">
                                    <label class="col-5 control-label has-star">
                                        <?= $education->getAttributeLabel('have_original'); ?>
                                    </label>

                                    <div class="col-7">
                                        <p class="form-control-static">
                                            <?= $education->haveOriginal ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (0 < $dividerCounts--) : ?>
                            <hr />
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /*INDIVIDUAL ACHIEVEMENTS CSS*/
    .ind .table-responsive {
        margin: 0;
    }

    @media screen and (max-width: 720px) {
        .ind .kv-grid-wrapper {
            height: 300px;
        }

        .ind .category-container {
            margin-left: 0;
        }
    }

    .ind .panel-body {
        padding: 0;
    }

    @media screen and (min-width: 720px) {
        .ind .kv-grid-wrapper {
            height: auto;
        }

        .ind .category-container {
            margin-left: 24px;
        }
    }

    .ind .kv-grid-wrapper table {
        border: none;
    }

    .margin-bottom {
        margin-bottom: 20px;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/view/block-individual-achievement',
                        'Заголовок в блоке ИД на стр. просмотра заявления: `Индивидуальные достижения`'
                    ) ?>
                </h4>
            </div>

            <div class="<?= (sizeof($individualAchievements->getModels()) > 0 ? 'ind' : '') ?>">
                <div class="card-body">
                    <?php if (sizeof($individualAchievements->getModels()) > 0) : ?>
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
                            'dataProvider' => $individualAchievements,
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
                                                'sandbox/view/block-individual-achievement',
                                                'Название группы достижений в таблице в блоке ИД на стр. просмотра заявления: `Достижение`'
                                            ),
                                            'options' => [
                                                'colspan' => 1,
                                                'class' => 'text-center'
                                            ]
                                        ],
                                        [
                                            'content' => Yii::t(
                                                'sandbox/view/block-individual-achievement',
                                                'Название группы реквизитов документов в таблице в блоке ИД на стр. просмотра заявления: `Реквизиты документа`'
                                            ),
                                            'options' => [
                                                'colspan' => 6,
                                                'class' => 'text-center'
                                            ]
                                        ],
                                    ],
                                    'options' => ['class' => 'skip-export']
                                ]
                            ],
                            'columns' => [
                                [
                                    'attribute' => 'achievementTypeName',
                                    'label' => Yii::t(
                                        'sandbox/view/block-individual-achievement',
                                        'Название колонки "achievementTypeName" в таблице в блоке ИД на стр. просмотра заявления: `Наименование`'
                                    )
                                ],
                                [
                                    'attribute' => 'documentTypeDocumentDescription',
                                    'label' => Yii::t(
                                        'sandbox/view/block-individual-achievement',
                                        'Название колонки "documentTypeDocumentDescription" в таблице в блоке ИД на стр. просмотра заявления: `Тип документа`'
                                    )
                                ],
                                [
                                    'attribute' => 'document_series',
                                    'label' => Yii::t(
                                        'sandbox/view/block-individual-achievement',
                                        'Название колонки "document_series" в таблице в блоке ИД на стр. просмотра заявления: `Серия`'
                                    )
                                ],
                                [
                                    'attribute' => 'document_number',
                                    'label' => Yii::t(
                                        'sandbox/view/block-individual-achievement',
                                        'Название колонки "document_number" в таблице в блоке ИД на стр. просмотра заявления: `Номер`'
                                    )
                                ],
                                [
                                    'attribute' => 'document_date',
                                    'label' => Yii::t(
                                        'sandbox/view/block-individual-achievement',
                                        'Название колонки "document_date" в таблице в блоке ИД на стр. просмотра заявления: `Дата выдачи`'
                                    )
                                ],
                                [
                                    'value' => function ($model) {
                                        return $model->contractor->name ?? '';
                                    },
                                    'label' => Yii::t(
                                        'sandbox/view/block-individual-achievement',
                                        'Название колонки "document_giver" в таблице в блоке ИД на стр. просмотра заявления: `Кем выдан`'
                                    )
                                ],
                                [
                                    'attribute' => 'id',
                                    'label' => Yii::t(
                                        'sandbox/view/block-individual-achievement',
                                        'Название колонки "id" в таблице в блоке ИД на стр. просмотра заявления: `Действия`'
                                    ),
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'col-1'],
                                    'value' => function ($model, $key) {
                                        $url = Url::toRoute(['site/downloadia', 'id' => $model->id]);
                                        $btnLabel = Yii::t(
                                            'sandbox/view/block-individual-achievement',
                                            'Подпись кнопки скачивания в таблице в блоке ИД на стр. просмотра заявления: `Скачать`'
                                        );
                                        return Html::a(
                                            "<i class='fa fa-save'></i> {$btnLabel}",
                                            $url,
                                            [
                                                'class' => 'btn btn-link',
                                                'download' => true,
                                                'disabled' => !$model->canDownload()
                                            ]
                                        );
                                    }
                                ]
                            ]
                        ]); ?>
                    <?php else : ?>
                        <div class="alert alert-info" role="alert">
                            <?= Yii::t(
                                'sandbox/view/block-individual-achievement',
                                'Текст пустой таблицы; в блоке ИД на стр. просмотра заявления: `Нет добавленных достижений`'
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/view/ege',
                        'Заголовок таблицы с наборами ВИ; на стр. просмотра заявления: `Наборы вступительных испытаний`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <div class="tab-content bachelor-tab">
                    <?= $this->render(
                        '_staticCompetitiveGroupEntranceTests',
                        [
                            'id' => $application->id,
                            'results' => $application->egeResults,
                            'competitiveGroupEntranceTest' => $competitiveGroupEntranceTest,
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$application->egeDisabled) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h4>
                        <?= Yii::t(
                            'sandbox/view/ege',
                            'Заголовок таблицы с результатами ВИ; на стр. просмотра заявления: `Результаты вступительных испытаний`'
                        ) ?>
                    </h4>
                </div>

                <div class="card-body">
                    <div class="tab-content bachelor-tab">
                        <?= $this->render(
                            '_staticEgeResult',
                            [
                                'application' => $application,
                                'egeResults' => $application->egeResults,
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/view/application/all',
                        'Заголовок блока НП на стр. просмотра заявления: `Направления подготовки`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <?php foreach ($application->specialities as $spec) : ?>
                    <?php $chosenSpeciality = $spec->speciality ?>

                    <div class="card mb-3">
                        <div class="card-header">
                            <span class="badge badge-primary spec-priority">
                                <?= $spec->specialityPriority->enrollment_priority; ?>
                            </span>

                            <strong>
                                <?= $chosenSpeciality->speciality_human_code; ?>
                                <?= $chosenSpeciality->directionRef->reference_name ?? ''; ?>
                            </strong>
                        </div>

                        <div class="card-body">
                            <p>
                                <span class="float-left">
                                    <?= $chosenSpeciality->educationLevelRef->reference_name ?? ''; ?>
                                </span>

                                <span class="float-right">
                                    <?= $chosenSpeciality->getAttributeLabel('finance_name') ?>:
                                    <?= $chosenSpeciality->educationSourceRef->reference_name ?? ''; ?>
                                </span>
                            </p>

                            <div style="clear:both;"></div>

                            <p>
                                <span class="float-left">
                                    <?= $chosenSpeciality->getAttributeLabel('eduform_name') ?>:
                                    <?= $chosenSpeciality->educationFormRef->reference_name ?? ''; ?>
                                </span>

                                <?php if ($chosenSpeciality->detailGroupRef) : ?>
                                    <span class="float-right">
                                        <?= $chosenSpeciality->getAttributeLabel('detail_group_name') ?>:
                                        <?= $chosenSpeciality->detailGroupRef->reference_name; ?>
                                    </span>
                                <?php endif; ?>
                            </p>

                            <div style="clear:both;"></div>

                            <p>
                                <span class="float-left">
                                    <?= $chosenSpeciality->subdivisionRef->reference_name ?? ''; ?>
                                </span>
                            </p>

                            <div style="clear:both;"></div>

                            <?php if ($spec->admissionCategory != null) : ?>
                                <p>
                                    <span class="float-left">
                                        <?= $spec->getAttributeLabel('admission_category_id') ?>:
                                        <?= $spec->admissionCategory->description; ?>
                                    </span>
                                </p>
                            <?php endif; ?>

                            <div style="clear:both;"></div>
                            <p class="text-right admission-agree">
                                <?php if ($spec->agreement != null) : ?>
                                    <?php if ($spec->agreement->file != null) : ?>
                                        <a target="_blank" href="<?= Url::to(['site/downloadagreement', 'id' => $spec->agreement->id]); ?>">
                                            <i class="fa fa-download" aria-hidden="true"></i>
                                            <?= Yii::t(
                                                'sandbox/view/application/all',
                                                'Подпись кнопки открытия модального окна согласия; на стр. просмотра заявления: `Прикрепить согласие на зачисление`'
                                            ) ?>
                                        </a>
                                    <?php else : ?>
                                        <span>
                                            <?= Yii::t(
                                                'sandbox/view/application/all',
                                                'Подпись кнопки открытия модального окна согласия; на стр. просмотра заявления: `Прикрепить согласие на зачисление`'
                                            ) ?>
                                        </span>
                                    <?php endif; ?>

                                    <br>

                                    <?php if ($spec->agreement->status == AdmissionAgreement::STATUS_NOTVERIFIED) : ?>
                                        <span>
                                            <?= Yii::t(
                                                'sandbox/view/application/all',
                                                'Информирующий текст, подтверждающий что согласие не принято в ПК; на стр. просмотра заявления: `Согласие не подтверждено ПК. После прикрепления согласия на зачисление необходимо нажать на кнопку "Согласие не подтверждено ПК"`'
                                            ) ?>
                                        </span>
                                    <?php else : ?>
                                        <span>
                                            <?= Yii::t(
                                                'sandbox/view/application/all',
                                                'Информирующий текст, подтверждающий что согласие принято в ПК; на стр. просмотра заявления: `Согласие не подтверждено ПК. После прикрепления согласия на зачисление необходимо нажать на кнопку "Согласие подтверждено ПК"`'
                                            ) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/view/accounting-benefits/all',
                        'Заголовок блока льгот на стр. просмотра заявления: `Копии документов подтверждающие преимущественные права, льготы и целевые направления`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <?php if ($application->bachelorPreferencesOlymp || $application->bachelorPreferencesSpecialRight || $application->bachelorTargetReceptions) : ?>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <h4>
                                        <?= Yii::t(
                                            'sandbox/moderate/accounting-benefits-block/privileges',
                                            'Заголовок таблицы льгот; в блоке льгот на стр. проверки анкеты поступающего: `Льготы`'
                                        ) ?>
                                    </h4>
                                </div>

                                <div class="col-md-9">
                                    <hr>
                                </div>

                                <div class="col-12">
                                    <div class="accounting-benefits-container">
                                        <div class="card mb-3">
                                            <?= $this->render(
                                                '@common/components/AccountingBenefits/_benefits',
                                                [
                                                    'id' => $resultBenefits['id'],
                                                    'model' => $resultBenefits['model'],
                                                    'items' => $resultBenefits['items'],
                                                    'canEdit' => $resultBenefits['canEdit'],
                                                    'action' => $resultBenefits['action'],
                                                    'itemsDoc' => $resultBenefits['itemsDoc'],
                                                    'providers' => $resultBenefits['providers'],
                                                    'dataProvider' => $resultBenefits['dataProvider'],
                                                    'preferences_comparison_helper' => null,
                                                    'application' => $application,
                                                    'benefitsService' => $benefitsService,
                                                ]
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <h4>
                                        <?= Yii::t(
                                            'sandbox/moderate/accounting-benefits-block/target-areas',
                                            'Заголовок таблицы ЦП; в блоке льгот на стр. проверки анкеты поступающего: `Целевые направления`'
                                        ) ?>
                                    </h4>
                                </div>

                                <div class="col-md-9">
                                    <hr>
                                </div>

                                <div class="col-12">
                                    <div class="accounting-benefits-container">
                                        <div class="card mb-3">
                                            <?= $this->render(
                                                '@common/components/TargetReception/_target_reception',
                                                [
                                                    'id' => $resultTargets['id'],
                                                    'model' => $resultTargets['model'],
                                                    'items' => $resultTargets['items'],
                                                    'canEdit' => $resultTargets['canEdit'],
                                                    'action' => $resultTargets['action'],
                                                    'providers' => $resultTargets['providers'],
                                                    'dataProvider' => $resultTargets['dataProvider'],
                                                    'targets_comparison_helper' => null,
                                                    'application' => $application,
                                                    'targetReceptionsService' => $targetReceptionsService,
                                                ]
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <h4>
                                        <?= Yii::t(
                                            'sandbox/view/accounting-benefits/block-olympiad',
                                            'Заголовок таблицы преимущественного права; в блоке льгот на стр. просмотра заявления: `Преимущественные права`'
                                        ) ?>
                                    </h4>
                                </div>

                                <div class="col-md-9">
                                    <hr>
                                </div>

                                <div class="col-12">
                                    <div class="accounting-benefits-container">
                                        <div class="card mb-3">
                                            <?= $this->render(
                                                '@common/components/AccountingBenefits/_olympiad',
                                                [
                                                    'id' => $resultOlympiads['id'],
                                                    'model' => $resultOlympiads['model'],
                                                    'items' => $resultOlympiads['items'],
                                                    'canEdit' => $resultOlympiads['canEdit'],
                                                    'action' => $resultOlympiads['action'],
                                                    'itemsDoc' => $resultOlympiads['itemsDoc'],
                                                    'providers' => $resultOlympiads['providers'],
                                                    'itemsOlymp' => $resultOlympiads['itemsOlymp'],
                                                    'dataProvider' => $resultOlympiads['dataProvider'],
                                                    'olympiads_comparison_helper' => null,
                                                    'application' => $application,
                                                    'olympiadsService' => $olympiadsService,
                                                ]
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <?= Yii::t(
                                    'sandbox/view/accounting-benefits/all',
                                    'Тест блока льгот если льготы отсутствуют на стр. просмотра заявления: `Нет данных о льготах, преимущественных правах и целевых направлениях для данного заявления`'
                                ) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?= $this->render(
            'partial/_copies_documents_panel',
            [
                'application' => $application,
                'questionary' => $questionary,
            ]
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/view/comments-moderator/all',
                        'Заголовок блока комментариев модератора на стр. просмотра заявления: `Комментарий модератора`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <p class="form-control-static application-comment">
                    <?= Html::encode($application->moderator_comment); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?= AttachmentWidget::widget([
            'disableFileSizeValidation' => true,
            'regulationConfigArray' => [
                'isReadonly' => true,
                'items' => $regulations,
            ],
            'showAttachments' => false
        ]) ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/view/comments-entering/all',
                        'Заголовок блока комментариев поступающего на стр. просмотра заявления: `Комментарии поступающего`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <?php if (sizeof($application->commentsComing) > 0) : ?>
                    <table class="table valign-middle">
                        <tr>
                            <th>
                                <?= Yii::t(
                                    'sandbox/view/comments-entering/all',
                                    'Заголовок колонки "Автор"; в блоке комментариев поступающего на стр. просмотра заявления: `Автор`'
                                ) ?>
                            </th>

                            <th>
                                <?= Yii::t(
                                    'sandbox/view/comments-entering/all',
                                    'Заголовок колонки "Комментарий"; в блоке комментариев поступающего на стр. просмотра заявления: `Комментарий`'
                                ) ?>
                            </th>

                            <th>
                                <?= Yii::t(
                                    'sandbox/view/comments-entering/all',
                                    'Заголовок колонки "Время"; в блоке комментариев поступающего на стр. просмотра заявления: `Время`'
                                ) ?>
                            </th>
                        </tr>
                        <?php foreach ($application->commentsComing as $commentsComingItem) : ?>
                            <tr>
                                <td>
                                    <?= $commentsComingItem->author->userProfile->getFullName(); ?>
                                </td>

                                <td class="application-comment">
                                    <?= Html::encode($commentsComingItem->comment); ?>
                                </td>

                                <td>
                                    <?= Yii::$app->formatter->asDatetime($commentsComingItem->created_at); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else : ?>
                    <div class="alert alert-info" role="alert">
                        <?= Yii::t(
                            'sandbox/view/comments-entering/all',
                            'Текст для пустой таблицы; в блоке комментариев поступающего на стр. просмотра заявления: `Нет комментариев`'
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($application->status == ApplicationInterface::STATUS_NOT_APPROVED || ($application->status == ApplicationInterface::STATUS_APPROVED && !$application->isArchive() && Yii::$app->configurationManager->getAllowReturnApprovedApplicationToModerating())) : ?>
    <div class="row">
        <div class="col-12">
            <?php $btnLabel = Yii::t(
                'sandbox/view/all',
                'Подпись кнопки возврата заявления обратно к модерации страницы просмотра анкеты поступающего: `Вернуть к модерации`'
            ) ?>

            <?= Html::a(
                '<i class="fa fa-check" aria-hidden="true"></i> ' . $btnLabel,
                ['sandbox/return-to-moderate', 'id' => $application->id],
                ['class' => 'btn btn-success float-right moderate-actor', 'id' => 'apply-button']
            ); ?>
        </div>
    </div>
<?php endif;
