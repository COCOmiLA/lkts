<?php








use common\models\User as MainAppUser;
use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\Avatar;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'My Profile');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-md-3 col-sm-4">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'profile']) ?>
    </div>
    <div class="col-md-6 col-sm-8">
        <div class="panel panel-default">
            <div class="panel-body">
                <h2>
                    <?php
                    if ($model->inherited_id != 0)
                        $inheritedUsername = (new MainAppUser)->find()->where(['id' => $model->inherited_id])->one()->username;
                    else
                        $inheritedUsername = $model->podiumName;
                    echo Html::encode($inheritedUsername);
                    ?>
                    <small>
                        <?= Html::encode($model->email) ?>
                        <?= Helper::roleLabel($model->role) ?>
                    </small>
                </h2>
                <p><?= Yii::t('podium/view', 'Whereabouts') ?>: <?= !empty($model->meta) && !empty($model->meta->location) ? Html::encode($model->meta->location) : '-' ?></p>
                <p><?= Yii::t('podium/view', 'Member since {date}', ['date' => Podium::getInstance()->formatter->asDatetime($model->created_at, 'long')]) ?> (<?= Podium::getInstance()->formatter->asRelativeTime($model->created_at) ?>)</p>
                <p>
                    <a href="<?= Url::to(['members/threads', 'id' => $model->id, 'slug' => $model->podiumSlug]) ?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Show all threads started by me') ?></a>
                    <a href="<?= Url::to(['members/posts', 'id' => $model->id, 'slug' => $model->podiumSlug]) ?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> <?= Yii::t('podium/view', 'Show all posts created by me') ?></a>
                </p>
            </div>
            <div class="panel-footer">
                <ul class="list-inline">
                    <li><?= Yii::t('podium/view', 'Threads') ?> <span class="badge"><?= $model->threadsCount ?></span></li>
                    <li><?= Yii::t('podium/view', 'Posts') ?> <span class="badge"><?= $model->postsCount ?></span></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-3 hidden-sm hidden-xs">
        <?= Avatar::widget([
            'author' => $model,
            'showName' => false
        ]) ?>
    </div>
</div><br>
