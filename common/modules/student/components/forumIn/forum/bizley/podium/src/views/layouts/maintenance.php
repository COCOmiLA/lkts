<?php








use common\modules\student\components\forumIn\forum\bizley\podium\src\assets\PodiumAsset;
use yii\helpers\Html;

PodiumAsset::register($this);
$this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>
    <div class="container">
        <?= $content ?>
    </div>
<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>
