<?php

use yii\helpers\Html;

$this->title = 'Добавить приемную кампанию (сопоставление с 1С)';

if ($dualReceptionCompany) {
    echo Html::tag(
        'div',
        'Выбранная приемная кампания уже добавлена на портал.',
        ['class' => 'alert alert-danger']
    );
}
?>

<?php echo $this->render('_form', [
    'model' => $model,
    'campaigns' => $campaigns
]) ?>
