<?php

use common\models\TimelineEvent;
use yii\base\InvalidArgumentException;
use yii\bootstrap4\LinkPager;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;











$this->title = Yii::t('backend', 'Хроника приложения');
$icons = ['user' => '<i class="fa fa-user bg-blue"></i>'];

?>

<?php

if ($result) {
    echo Html::tag(
        'div',
        Html::a(
            'Есть изменения в БД, которые необходимо применить',
            Url::toRoute(['/update']),
            ['class' => "alert-link"]
        ),
        [
            'class' => 'alert alert-warning',
            'style' => 'background-color: var(--yellow) !important'
        ]
    );
}

if ($hasMissingEnvironmentSettings) {
    echo Html::tag(
        'div',
        Html::a(
            Yii::t('backend', 'Не все параметры окружения заполнены!'),
            Url::toRoute(['/env-settings/index']),
            ['class' => "alert-link"]
        ),
        [
            'class' => 'alert alert-warning',
            'style' => 'background-color: var(--yellow) !important'
        ]
    );
}

if ($needToSetCode) {
    echo Html::tag(
        'div',
        Html::a(
            'Есть незаполненные коды по умолчанию',
            Url::toRoute(['/settings/code']),
            ['class' => "alert-link"]
        ),
        [
            'class' => 'alert alert-warning',
            'style' => 'background-color: var(--yellow) !important'
        ]
    );
}

if ($mailError) {
    echo Html::tag(
        'div',
        'Не указаны настройки для отправки электронной почты',
        [
            'class' => 'alert alert-warning',
            'style' => 'background-color: var(--yellow) !important; color: var(--dark) !important'
        ]
    );
}

if ($timeZoneError) {
    echo Html::tag(
        'div',
        '<strong>Внимание!</strong> Часовой пояс не установлен. Произведите настройку "date.timezone" в "php.ini"',
        [
            'class' => 'alert alert-warning',
            'style' => 'background-color: var(--yellow) !important; color: var(--dark) !important'
        ]
    );
}
?>

<?php Pjax::begin(); ?>

<?php if ($dataProvider->count > 0) : ?>
    <div class="timeline">
        <?php foreach ($dataProvider->getModels() as $model) : ?>
            <?php if (!isset($date) || $date != Yii::$app->formatter->asDate($model->created_at)) : ?>
                <div class="time-label">
                    <span class="bg-blue">
                        <?php echo Yii::$app->formatter->asDate($model->created_at) ?>
                    </span>
                </div>

                <?php $date = Yii::$app->formatter->asDate($model->created_at) ?>
            <?php endif; ?>

            <div>
                <?php try {
                    $viewFile = sprintf('%s/%s', $model->category, $model->event);
                    echo $this->render($viewFile, ['model' => $model]);
                } catch (InvalidArgumentException $e) {
                    echo $this->render('@backend/views/timeline-event/_item', ['model' => $model]);
                } ?>
            </div>
        <?php endforeach; ?>

        <div>
            <i class="fa fa-clock-o"></i>
        </div>
    </div>
<?php else : ?>
    <?php echo Yii::t('backend', 'Событий нет') ?>
<?php endif; ?>

<div class="col-md-12 text-center">
    <?php echo LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'options' => ['class' => 'pagination']
    ]) ?>
</div>

<?php Pjax::end();
