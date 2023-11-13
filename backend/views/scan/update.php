<?php

use common\models\AttachmentType;


$this->title = 'Обновление типа скан-копии ' . $model->name;

?>

<?php if (isset($model->admissionCampaignRef)) : ?>
    <div class="alert alert-info">
        Данные получены из 1С
    </div>
<?php endif ?>

<?php echo $this->render(
    '_form',
    [
        'model' => $model,
        'entities' => $entities,
        'document_types' => $document_types
    ]
);
