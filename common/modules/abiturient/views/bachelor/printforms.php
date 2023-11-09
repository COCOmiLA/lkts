<?php

use yii\helpers\Url;


$this->title = Yii::$app->name . ' | ' . 'Личный кабинет поступающего';

?>
<?php echo $this->render('../abiturient/_abiturientheader', [
    'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
]); ?>
<div class="row">
    <div class="col-12">
        <?php if (
            ($application->type->blocked || $application->type->stageTwoStarted()) &&
            $blockTopText = Yii::$app->configurationManager->getText('block_top_text', $application->type ?? null)
        ) : ?>
            <div class="alert alert-info" role="alert">
                <?php echo $blockTopText; ?>
            </div>
        <?php endif; ?>

        <div class="">
            <h3 class="float-left">Печатные формы:</h3>
        </div>
        <div class="col-12">
            <?php if (isset($printForms) && $printForms) : ?>
                <ul>
                    <?php foreach ($printForms as $printForm) : ?>
                        <li><a target="_blank" href="<?php echo Url::toRoute(['site/download-form', 'id' => $printForm->model->id, 'type' => $printForm->type]) ?>">
                                <?php print $printForm->title; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <div class="alert alert-info" role="alert">
                    Нет доступных печатных форм
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>