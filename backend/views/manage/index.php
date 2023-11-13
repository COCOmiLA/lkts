<?php

use backend\models\ManageAC;
use backend\models\ManagerAllowChat;
use backend\models\RBACAuthItem;
use common\modules\abiturient\models\bachelor\ApplicationType;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;

$this->title = Yii::t('backend', 'Управление приемными кампаниями модератора');
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
            'label' => Yii::t('backend', 'Имя модератора'),
        ],
        [
            'attribute' => 'name',
            'label' => Yii::t('backend', 'Название приемной кампании'),
            'value' => function ($data) {
                $tnManageAC = ManageAC::tableName();
                $tnApplicationType = ApplicationType::tableName();

                $manageAC = ApplicationType::find()
                    ->select("{$tnApplicationType}.name")
                    ->leftJoin(
                        $tnManageAC,
                        "{$tnApplicationType}.id = {$tnManageAC}.application_type_id"
                    )
                    ->andWhere(["{$tnManageAC}.rbac_auth_assignment_user_id" => $data['user_id']])
                    ->orderBy("{$tnApplicationType}.id")
                    ->column();

                if (empty($manageAC)) {
                    return Yii::t('backend', 'Для данного модератора нет приемных кампаний');
                }

                return join(', ', $manageAC);
            }
        ],
        [
            'attribute' => 'allowChat',
            'label' => Yii::t('backend', 'Разрешение работы с чатом'),
            'value' => function ($data) {
                $tnManageAC = ManageAC::tableName();
                $tnManagerAllowChat = ManagerAllowChat::tableName();

                $allowChat = ManagerAllowChat::find()
                    ->leftJoin(
                        $tnManageAC,
                        "{$tnManagerAllowChat}.manager_id = {$tnManageAC}.rbac_auth_assignment_user_id"
                    )
                    ->andWhere(["{$tnManageAC}.rbac_auth_assignment_user_id" => $data['user_id']])
                    ->orderBy("{$tnManagerAllowChat}.id")
                    ->exists();

                if ($allowChat) {
                    return Yii::t('backend', 'Да');
                } else {
                    return Yii::t('backend', 'Нет');
                }
            }
        ],
        [
            'attribute' => 'managerNickname',
            'label' => Yii::t('backend', 'Никнейм менеджера в чате'),
            'value' => function ($data) {
                $tnManageAC = ManageAC::tableName();
                $tnManagerAllowChat = ManagerAllowChat::tableName();

                $allowChat = ManagerAllowChat::find()
                    ->leftJoin(
                        $tnManageAC,
                        "{$tnManagerAllowChat}.manager_id = {$tnManageAC}.rbac_auth_assignment_user_id"
                    )
                    ->andWhere(["{$tnManageAC}.rbac_auth_assignment_user_id" => $data['user_id']])
                    ->orderBy("{$tnManagerAllowChat}.id")
                    ->one();

                if ($allowChat && $allowChat->nickname) {
                    return $allowChat->nickname;
                } else {
                    return '-';
                }
            }
        ],
        [
            'class' => ActionColumn::class,
            'visible' => Yii::$app->user->can(RBACAuthItem::ADMINISTRATOR),
            'urlCreator' => function ($action, $model, $key, $index) {
                return ['manage/' . $action, 'id' => $model['user_id']];
            },
            'header' => false,
            'headerOptions' => ['width' => '50'],
            'template' => '{view} {update}',
            'buttons' => [
                'view' => function ($data) {
                    return Html::a('<i class="fa fa-eye"></i>', $data);
                },
                'update' => function ($data) {
                    return Html::a('<i class="fa fa-pencil"></i>', $data);
                },
            ],
        ],
    ],
]);
