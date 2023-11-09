<?php

use backend\assets\ParentDataSettingsAsset;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

ParentDataSettingsAsset::register($this);

$appLanguage = Yii::$app->language;
$this->title = 'Настройки блока данных о родителях или законных представителях';
?>

<div class="col-xs-12 sandbox-settings">
    <?php $form = ActiveForm::begin([
        'id' => 'text-form',
        'options' => ['class' => 'form-horizontal', 'name' => 'TextForm'],
        'fieldConfig' => [
            'template' => "{input}\n{error}"
        ]
    ]); ?>
    <table class="table text-table">
        <thead>
            <tr>
                <th>Описание</th>
                <th>Код</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 0; ?>
            <?php foreach ($codes as $code) : ?>
                <tr id="<?php echo $code->name ?>">
                    <td style="width: 25%;">
                        <span><?= $code->description; ?></span>
                        <?= Html::hiddenInput('ParentDataSetting[' . $i . '][id]', $code->id); ?>
                    </td>

                    <td style="width: 65%;">
                        <?php
                        $data = ['1' => 'Да', '0' => 'Нет'];

                        echo Select2::widget([
                            'language' => $appLanguage,
                            'model' => $code,
                            'name' => 'ParentDataSetting[' . $i . '][value]',
                            'attribute' => 'value',
                            'data' => $data,
                            'options' => [
                                'placeholder' => 'Выберите ...',
                                'name' => 'ParentDataSetting[' . $i . '][value]',
                                'id' => 'codesetting-' . $code->name,
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                                'multiple' => false
                            ],
                        ]);
                        ?>
                    </td>
                </tr>
                <?php $i++; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary pull-right']); ?>
    <?php ActiveForm::end() ?>
</div>