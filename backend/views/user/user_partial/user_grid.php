<?php

use backend\models\search\UserSearch;
use common\grid\EnumColumn;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\gridview\ActionColumn as GridviewActionColumn;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;








$exceptions = array('administrator', 'manager');
$loggedId = Yii::$app->user->getId();

?>

<div style="margin-top: 10px;">
    <span>
        <?= Html::button('Выделить все', [
            'class' => 'btn btn-outline-secondary',
            'id' => 'checkAll',
        ]) ?>
    </span>

    <span>
        <?= Html::button('Снять выделение', [
            'class' => 'btn btn-outline-secondary',
            'id' => 'uncheckAll',
        ]) ?>
    </span>
</div>

<?php if (isset($error_message) && !empty($error_message)) : ?>
    <div class="alert alert-danger" style="margin-top: 15px;" role="alert">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<?php echo GridView::widget([
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
    'filterModel' => $searchModel,
    'id' => 'custom-users-grid',
    'filterUrl' => ['user/index'],
    'columns' => [
        [
            'class' => CheckboxColumn::class,
            'contentOptions' => ['style' => 'width: 30px'],
            'checkboxOptions' => function ($model, $key, $index, $column) use ($exceptions) {
                $role = Yii::$app->authManager->getRolesByUser($model->id);
                $result = array_diff(array_keys($role), $exceptions);
                if (!empty($result)) {
                    return ['value' => $key];
                }
                return [
                    'disabled' => true,
                    'class' => 'disabled_checkbox',
                    'style' => ['display' => 'none'],
                ];
            },
        ],
        'id',
        [
            'attribute' => 'block_status',
            'label' => 'Статус блокировки',
            'value' => function (User $model) {
                $blockStatusTooltip = array_filter(
                    array_unique(array_map(
                        function (BachelorApplication $application) {
                            if ($application->block_status) {
                                return ArrayHelper::getValue($application, 'type.rawCampaign.name', '');
                            }
                            return '';
                        },
                        $model->rawApplications
                    )),
                    function ($blockStatus) {
                        return !empty($blockStatus);
                    }
                );

                if (empty($blockStatusTooltip)) {
                    return '';
                }

                $tooltipString = implode(
                    ", ",
                    $blockStatusTooltip
                );

                return Html::tag(
                    'i',
                    null,
                    [
                        'class' => 'fa fa-lock',
                        'title' => $tooltipString,
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'right',
                    ]
                );
            },
            'format' => 'raw',
            'filter' => Html::dropDownList(
                $searchModel->formName() . '[block_status]',
                $searchModel->block_status,
                [
                    null => 'Все',
                    BachelorApplication::BLOCK_STATUS_ENABLED => 'Заблокировано',
                ]
            ),
        ],
        [
            'attribute' => 'admission_campaign',
            'label' => 'Приемная кампания',
            'value' => function (User $model) {
                $campaign_names = array_map(function (BachelorApplication $application) {
                    $campaign = $application->type->rawCampaign;
                    return $campaign->name ?? null;
                }, $model->applications);
                return implode('<br>', array_filter(array_unique($campaign_names)));
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'group_name',
            'label' => 'Конкурсная группа',
            'value' => function (User $model) {
                $group_names = array_map(
                    function (BachelorApplication $application) {
                        return array_map(
                            function (BachelorSpeciality $spec) {
                                return $spec->speciality->group_name;
                            },
                            $application->specialities
                        );
                    },
                    $model->applications
                );
                $group_names = array_merge(...$group_names);
                if (!is_array($group_names)) {
                    return '';
                }

                return implode('<br>', array_filter(array_unique($group_names)));
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'fio',
            'label' => 'ФИО',
            'value' => function (User $model) {
                return $model->absFullName;
            },
            'format' => 'raw',
        ],
        'username',
        'email:email',
        [
            'class' => EnumColumn::class,
            'attribute' => 'role',
            'enum' => User::getRoles(),
            'filter' => User::getRoles()
        ],
        [
            'class' => EnumColumn::class,
            'attribute' => 'status',
            'enum' => User::getStatuses(),
            'filter' => User::getStatuses()
        ],
        'created_at:datetime',
        'logged_at:datetime',
        [
            'class' => EnumColumn::class,
            'attribute' => 'is_archive',
            'label' => 'Запись в архиве',
            'enum' => User::getArchives(),
            'contentOptions' => ['style' => 'width: 130px'],
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{view} {update} {delete} {transfer} {juxtapose}',
            'buttons' => [
                'transfer' => function ($url, $model) use ($loggedId) {
                    if ($model->id !== $loggedId) {
                        return Html::a(
                            '<i class="fa fa-exchange"></i>',
                            '/transfer/transfer?id=' . $model->id,
                            GridviewActionColumn::buttonOptions(
                                [
                                    'class' => '',
                                    'title' => 'Сменить пользователя'
                                ]
                            )
                        );
                    }
                    return '';
                },
                
                'juxtapose' => function ($url, $model) use ($loggedId) {
                    if ($model->id !== $loggedId) {
                        return Html::a(
                            '<i class="fa fa-adjust"></i>',
                            ['/juxtapose/index', 'user_id' => $model->id],
                            GridviewActionColumn::buttonOptions(
                                [
                                    'class' => '',
                                    'title' => 'Сопоставить пользователя с физ. лицом'
                                ]
                            )
                        );
                    }
                    return '';
                }
            ],
        ],
    ],
]);
