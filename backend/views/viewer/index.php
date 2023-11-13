<?php

use backend\models\RBACAuthItem;
use backend\models\ViewerAdmissionCampaignJunction;
use common\modules\abiturient\models\bachelor\ApplicationType;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;

$this->title = 'Управление приемными кампаниями просмотра заявлений';
$this->params['breadcrumbs'][] = $this->title;

echo GridView::widget([
    'hover' => true,
    'headerContainer' => ['class' => 'thead-light'],
    'tableOptions' => ['class' => 'table-sm'],
    'striped' => false,
    'summary' => false,
    'pager' => [
        'firstPageLabel' => '<<',
        'prevPageLabel' => '<',
        'nextPageLabel' => '>',
        'lastPageLabel' => '>>',
    ],
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'username',
            'label' => 'Имя модератора',
        ],
        [
            'attribute' => 'name',
            'label' => 'Название приемной кампании',
            'value' => function ($data) {
                $tnApplicationType = ApplicationType::tableName();
                $tnViewerAdmissionCampaignJunction = ViewerAdmissionCampaignJunction::tableName();

                $admissionCampaigns = ApplicationType::find()
                    ->select("{$tnApplicationType}.name")
                    ->leftJoin($tnViewerAdmissionCampaignJunction, "{$tnApplicationType}.id = {$tnViewerAdmissionCampaignJunction}.application_type_id")
                    ->andWhere(["{$tnViewerAdmissionCampaignJunction}.user_id" => $data['user_id']])
                    ->orderBy("{$tnApplicationType}.id")
                    ->column();

                if (empty($admissionCampaigns)) {
                    return 'Для данного пользователя нет приёмных кампаний для просмотра заявлений';
                }

                return join(', ', $admissionCampaigns);
            }

        ],
        [
            'class' => ActionColumn::class,
            'visible' => Yii::$app->user->can(RBACAuthItem::ADMINISTRATOR),
            'urlCreator' => function ($action, $model, $key, $index) {
                return ['viewer/' . $action, 'id' => $model['user_id']];
            },
            'header' => false,
            'headerOptions' => ['width' => '50'],
            'template' => '{view} {update}',
            'buttons' => [
                'view' => function ($data) {
                    return Html::a('<span class="fa fa-eye"></span>', $data);
                },
                'update' => function ($data) {
                    return Html::a('<span class="fa fa-pencil"></span>', $data);
                },
            ],
        ],
    ],
]);
