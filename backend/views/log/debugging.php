<?php







use kartik\widgets\SwitchInput;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$switchChange_script = <<<JS
    function switcherChanged() {
      var dummy_table = $('.dummy_table');
      var dummy_form = $('.new-dummy-record-form');
    
      if (this.checked) {
        dummy_table.show();
        dummy_form.show();
      } else {
        dummy_table.hide();
        dummy_form.hide();
      }
    }
JS;
$debug_change_script = <<<JS
    function checkDebugSettingsInSwitcher() {
      var normal = $('[name$="[debugging_enable]"]');
      var xml = $('[name$="[xml_debugging_enable]"]');
    
      if (normal.is(':checked') && xml.is(':checked')) {
        $('.debugging-warn').show();
      } else {
        $('.debugging-warn').hide();
      }
    }
JS;

$this->title = 'Отладка SOAP';
?>
    <div class="system-log-index">
        <?php if (isset($model)) {
            $form = ActiveForm::begin(['method' => 'post']);
            echo $form->field($model, 'enable_api_debug')->widget(SwitchInput::class, []);
            echo $form->field($model, 'debugging_enable')->widget(SwitchInput::class, [
                'pluginEvents' => [
                    "switchChange.bootstrapSwitch" => "$debug_change_script",
                ]
            ]);
            echo $form->field($model, 'xml_debugging_enable')->widget(SwitchInput::class, [
                'pluginEvents' => [
                    "switchChange.bootstrapSwitch" => "$debug_change_script",
                ]
            ]);
            ?>
            <div class="alert alert-warning debugging-warn" style="display: none;">
                При включении обеих функций производительность системы может значительно уменьшиться
            </div>
            <?php
            echo $form->field($model, 'enable_dummy_soap_mode')->widget(SwitchInput::class, [
                'pluginEvents' => [
                    "switchChange.bootstrapSwitch" => "$switchChange_script",
                ]
            ]);
            echo $form->field($model, 'model_validation_debugging_enable')->widget(SwitchInput::class);
            echo $form->field($model, 'enable_logging_for_dictionary_soap')->widget(SwitchInput::class);
            echo $form->field($model, 'enable_logging_for_kladr_soap')->widget(SwitchInput::class);
            echo Html::tag(
                'div',
                Html::submitButton('Обновить', ['class' => 'btn btn-primary']),
                ['class' => 'form-group']
            );
            ActiveForm::end();
            if ($model->enable_dummy_soap_mode) {
                echo $this->render('@backend/views/log/_dummy_soap_table');
                if (!isset($newSoapModel)) {
                    $newSoapModel = new \common\models\DummySoapResponse();
                }
                echo $this->render('@backend/views/log/_dummy_soap_add_new', compact('newSoapModel'));
            }

        } else {
            echo Html::tag(
                'div',
                'Для работы сервиса отсутствуют необходимые таблицы в базе данных. Перейдите на <a href="/admin/update/index">страницу обновлений</a>.',
                ['class' => 'alert alert-danger', 'role' => 'alert']
            );
        } ?>
    </div>
<?php
$script = <<<JS
    function checkDebugSettings() {
      var normal = $('[name$="[debugging_enable]"]');
      var xml = $('[name$="[xml_debugging_enable]"]');
    
      if (normal.is(':checked') && xml.is(':checked')) {
        $('.debugging-warn').show();
      } else {
        $('.debugging-warn').hide();
      }
    }
    
    checkDebugSettings();
JS;
$this->registerJs($script);
