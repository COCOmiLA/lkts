<?php

use backend\assets\UserSearchAsset;
use backend\models\search\UserSearch;
use kartik\helpers\Html as HelpersHtml;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;








UserSearchAsset::register($this);

$this->title = Yii::t('backend', 'Пользователи');
$this->params['breadcrumbs'][] = $this->title;

?>

<span>
    <?= Html::a(
        Yii::t('backend', 'Создание {modelClass}', ['modelClass' => 'User']),
        ['create'],
        ['class' => 'btn btn-success']
    ) ?>
</span>

<span>
    <?= Html::button('Удалить с портала', ['class' => 'btn btn-outline-secondary', 'id' => 'del-users']) ?>
</span>

<span>
    <?= Html::button('Переместить в архив', ['class' => 'btn btn-outline-secondary', 'id' => 'to-archive']) ?>
</span>

<span>
    <?= Html::button('Восстановить из архива', ['class' => 'btn btn-outline-secondary', 'id' => 'from-archive']) ?>
</span>

<span>
    <?= Html::button('Обезличить пользователей', ['class' => 'btn btn-outline-secondary', 'id' => 'depersonalize-users']) ?>
</span>

<span>
    <?= Html::button('Снять блокировку с заявлений', ['class' => 'btn btn-outline-secondary', 'id' => 'remove-applications-blocking']) ?>
</span>

<span>
    <?= Html::button(
        'Удалить всех пользователей',
        [
            'id' => 'del-all-users',
            'data-toggle' => 'tooltip',
            'class' => 'btn btn-danger',
            'data-placement' => 'bottom',
            'title' => 'ВНИМАНИЕ!!! Данное действие безвозвратно удалит всех пользователей не относящихся к числу модераторов или администраторов',
        ]
    ) ?>
</span>

<div>
    <div id="custom-user-render">
        <?= $this->render(
            'user_partial/user_grid',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <?= HelpersHtml::radioButtonGroup(
            "{$searchModel->formName()}[pageSize]",
            $searchModel->pageSize,
            ArrayHelper::map(
                [20, 50, 100, 200, 500],
                function ($data) {
                    return $data;
                },
                function ($data) {
                    return $data;
                }
            ),
            ['itemOptions' => ['labelOptions' => [
                'onclick' => 'window.changePagination($(this))',
                'class' => 'btn btn-success pagination_size',
            ]]]
        ) ?>
    </div>

    <div class="col-6">
        <?= Html::button(
            '<i class="fa fa-arrow-up"></i> Наверх',
            [
                'id' => 'btn_to_up_scroll',
                'onclick' => 'window.toTop()',
                'class' => 'btn btn-warning float-right',
            ]
        ) ?>
    </div>
</div>