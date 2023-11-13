<?php





use kartik\form\ActiveForm;
use yii\bootstrap4\Html;

echo Html::beginTag('div', ['class' => 'new-dummy-record-form']);
?>
    <h2>Добавить новую soap заглушку</h2>
<?php
$form = ActiveForm::begin([
    'method' => 'post',
    'action' => ['/log/debugging'],
]);
echo $form->field($newSoapModel, 'method_name');
$code_sample = "
<SoapMethodNameResponse>
    <return>
        Ваши теги soap тут   
    </return>
</SoapMethodNameResponse>
";
?>
    <h3>Пример soap ответа:</h3>
    <pre>
            <?php echo Html::encode(trim((string)$code_sample,' \t\n\r\0\x0B')); ?>
    </pre>
<?php
echo $form->field($newSoapModel, 'method_response')->textarea(['rows' => 10]); ?>
    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end();
echo Html::endTag('div');
