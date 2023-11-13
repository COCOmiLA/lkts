<?php

use yii\web\View;






$this->title = Yii::t('backend', 'Очистка данных');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php echo $this->render('_clean_log');
