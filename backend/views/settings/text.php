<?php

use common\models\settings\TextSetting;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Настройки текстов';

?>

<div id="sandbox-settings-id">
    <?php $search_form = ActiveForm::begin([
        'id' => 'text-form',
        'method' => 'get',
        'action' => ['/settings/text'],
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{input}\n{error}"
        ]
    ]); ?>

    <div>
        <label>Язык</label>
        <?= Select2::widget([
            'name' => 'language',
            'data' => $languages,
            'value' => $language,
            'options' => [
                'multiple' => false,
                'placeholder' => 'Выберите язык ...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#sandbox-settings-id',
            ],
        ]); ?>
    </div>

    <br>

    <div>
        <label>Приёмная кампания</label>
        <?= Select2::widget([
            'name' => 'application_type',
            'data' => $application_types,
            'value' => $application_type,
            'options' => [
                'multiple' => false,
                'placeholder' => 'Выберите кампанию ...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#sandbox-settings-id',
            ],
        ]); ?>
    </div>

    <br>

    <?php echo Html::submitButton('Показать', ['class' => 'btn btn-primary', 'style' => 'margin-bottom: 20px;']); ?>
    <?php ActiveForm::end() ?>

    <?php if ($all_texts) : ?>
        <div class="alert alert-info">
            <p>
                Внимание! Если в месте отображения текста на страницах портала будет невозможно определить приёмную
                кампанию (например, поступающий ещё не выбрал конкретную ПК), то будут отображаться тексты из категории
                ПК "По умолчанию".
            </p>
        </div>
        <?php $form = ActiveForm::begin([
            'id' => 'text-form',
            'options' => ['class' => 'form-horizontal', 'name' => 'TextForm'],
            'fieldConfig' => [
                'template' => "{input}\n{error}"
            ]
        ]); ?>

        <div class="accordion" id="accordion" role="tablist" aria-multiselectable="true">
            <?php foreach ($all_texts as $category => $texts) : ?>
                <?php
                $category_name = $categories[$category];
                ?>
                <?php if ($texts) : ?>
                    <div class="card">
                        <div class="card-header" role="tab" id="heading<?php echo $category; ?>">
                            <h4 class="card-title">
                                <a class="btn btn-link btn-block" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $category; ?>" aria-expanded="false" aria-controls="collapse<?php echo $category; ?>">
                                    <?php echo $category_name; ?>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse<?php echo $category; ?>" class="collapse" role="tabcard" aria-labelledby="heading<?php echo $category; ?>">
                            <div class="card-body">
                                <table class="table text-table">
                                    <tr>
                                        <th>Описание</th>
                                        <th>Текст</th>
                                    </tr>
                                    <?php foreach ($texts as $text_index => $text_config) : ?>
                                        <?php
                                        $full_text_config = ArrayHelper::merge($text_config, [
                                            'language' => $language,
                                            'application_type' => $application_type
                                        ]);
                                        $encoded_full_text_config = json_encode($full_text_config);
                                        $text = TextSetting::find()
                                            ->andWhere($full_text_config)
                                            ->one();
                                        $default_value = ArrayHelper::getValue($text, 'value');
                                        if (!$text) {
                                            $default_value = TextSetting::find()
                                                ->andWhere(ArrayHelper::merge($full_text_config, ['application_type' => TextSetting::APPLICATION_TYPE_DEFAULT]))
                                                ->select(['value'])
                                                ->scalar();
                                        }
                                        ?>
                                        <tr>
                                            <td style="width: 25%;">
                                                <span><?php echo $text_config['description']; ?></span>
                                                &#160;
                                                <?php if ($message = $text_config['tooltip_description']) : ?>
                                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?= $message ?>"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td style="width: 65%;">
                                                <?php echo Html::textInput(
                                                    'TextSetting[' . $encoded_full_text_config . '][value]',
                                                    $default_value,
                                                    ['class' => 'form-control']
                                                ); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary pull-right']); ?>
        <?php ActiveForm::end() ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-12 sandbox-settings">
            <label><?php echo Yii::t('backend', 'Восстановить настройки по умолчанию для всех ПК'); ?></label>
            <div class="form-group">
                <?php $confirmBtn = Html::a(Yii::t('backend', 'Восстановить'), Url::to(['reset-text-settings']), [
                    'class' => 'btn btn-primary',
                ]); ?>
                <?php $cancelBtn = Html::button('Отмена', [
                    'data-dismiss' => 'modal',
                    'class' => 'btn btn-default',
                ]); ?>

                <?php Modal::begin([
                    'id' => 'reset-text-settings-modal',
                    'title' => '<h4>Подтвердите действие</h4>',
                    'toggleButton' => [
                        'class' => 'btn btn-danger',
                        'tag' => 'button',
                        'label' => Yii::t('backend', 'Восстановить')
                    ],
                    'footer' => $confirmBtn . $cancelBtn
                ]); ?>

                <?php echo Yii::t('backend', 'Настройки текстов для всех ПК будут заменены настройками по умолчанию'); ?>

                <?php Modal::end(); ?>
            </div>
        </div>
    </div>
</div>