<?php

use common\components\AttachmentManager;
use common\components\ini\iniGet;
use common\models\EmptyCheck;
use kartik\widgets\FileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

$this->title = 'Настройки песочницы';

$appLanguage = Yii::$app->language;

?>

<?php $form = ActiveForm::begin([
    'id' => 'sandbox-form',
    'options' => ['name' => 'SandboxForm'],
    'fieldConfig' => ['template' => '{input}{error}']
]); ?>
    <div class="row">
        <div class="col-12">
            <?php echo $form->field($sandbox_enabled, 'value')->checkbox(['label' => 'Включить песочницу']); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']); ?>
        </div>
    </div>
<?php ActiveForm::end();