<?php

use common\components\CodeSettingsManager\CodeSettingsManager;
use common\models\dictionary\DocumentType;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$uid = Yii::$app->configurationManager->getCode('identity_docs_guid');
$document_type = null;
$documentTypeEntity = CodeSettingsManager::GetEntityByCode('russian_passport_guid');
if (!is_null($documentTypeEntity)) {
    $document_type = $documentTypeEntity->id;
}
$parent = DocumentType::findByUID($uid);
if ($parent) {
    $docs = DocumentType::find()
        ->notMarkedToDelete()
        ->active()
        ->andWhere(['parent_key' => $parent->ref_key])
        ->andWhere(["is_folder" => false])
        ->orderBy(['ref_key' => SORT_DESC])->all();
} else {
    $docs = [];
}
$docs_all = ArrayHelper::map($docs, 'id', 'description');

$isReadonly = $isReadonly ?? false;
$modalPassportHeaderCreate = $modalPassportHeaderCreate ?? Yii::t(
    'abiturient/questionary/passport-modal',
    'Заголовок модального окна для создания паспорта на странице анкеты поступающего: `Создать`'
);
$modalPassportHeaderEditTemp = Yii::t(
    'abiturient/questionary/passport-modal',
    'Заголовок модального окна для редактирования паспорта на странице анкеты поступающего: `Редактировать`'
);
if ($isReadonly) {
    $modalPassportHeaderEditTemp = Yii::t(
        'abiturient/questionary/passport-modal',
        'Заголовок модального окна для просмотра паспорта на странице анкеты поступающего: `Просмотреть`'
    );
}
$modalPassportHeaderEdit = $modalPassportHeaderEdit ?? $modalPassportHeaderEditTemp;


Modal::begin([
    'title' => Html::tag('h4', $modalPassportHeaderCreate),
    'size' => 'modal-lg',
    'id' => "create_modal_passport",
    'options' => [
        'class' => 'passport-modal',
        'tabindex' => false,
    ],
]); ?>

<div class='row'>
    <div class='col-12'>
        <?= $this->render(
            '_passportForm',
            [
                'model' => new PassportData(),
                'passportTypes' => $docs_all,
                'document_type' => $document_type,
                'isReadonly' => $isReadonly,
                'allowAddNewFileToOldPassportAfterApprove' => true,
                'allowDeleteFileFromOldPassportAfterApprove' => true,
                'keynum' => 0,
                'action' => Url::to($action),
                'application' => $application ?? null,
            ]
        ); ?>
    </div>
</div>
<?php Modal::end();

$allowAddNewFileToOldPassportAfterApprove = QuestionarySettings::getSettingByName('allow_add_new_file_to_passport_after_approve');
$allowDeleteFileFromOldPassportAfterApprove = QuestionarySettings::getSettingByName('allow_delete_file_from_passport_after_approve');

foreach ($passports->getModels() as $key => $model) {
    $edit_action = [
        $action,
        'id' => $model->id
    ];
    if (isset($application)) {
        $edit_action['application_id'] = $application->id;
    }

    
    Modal::begin([
        'title' => Html::tag('h4', $modalPassportHeaderEdit),
        'size' => 'modal-lg',
        'id' => "edit_modal_passport_{$model->id}",
        'options' => [
            'class' => 'passport-modal',
            'tabindex' => false,
        ],
    ]);
    echo "<div class='row'>";
    echo "<div class='col-12'>";
    echo $this->render(
        '_passportForm',
        [
            'model' => $model,
            'passportTypes' => $docs_all,
            'document_type' => $document_type,
            'isReadonly' => $isReadonly,
            'allowAddNewFileToOldPassportAfterApprove' => $allowAddNewFileToOldPassportAfterApprove,
            'allowDeleteFileFromOldPassportAfterApprove' => $allowDeleteFileFromOldPassportAfterApprove,
            'keynum' => $model->id,
            'action' => Url::to($edit_action),
            'application' => $application ?? null,
        ]
    );
    echo "</div>";
    echo "</div>";
    Modal::end();
}

$this->registerJsVar('modalPassportHeaderCreate', $modalPassportHeaderCreate);
$this->registerJsVar('modalPassportHeaderEdit', $modalPassportHeaderEdit);
