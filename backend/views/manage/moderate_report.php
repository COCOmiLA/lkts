<?php

use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\ModerateHistory;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\widgets\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$appLanguage = Yii::$app->language;

$this->title = 'Отчёт по работе модераторов';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="user-index card-body">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'username')
                ->dropDownList(
                    $moderateNameArray,
                    ['prompt' => 'Выберите имя']
                )
                ->label('Имя модератора'); ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($model, 'campaign')->dropDownList(
                $applicationTypeArray,
                ['prompt' => 'Выберите ПК']
            )
                ->label('Приёмная кампания'); ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($model, 'timeStart')->widget(
                DatePicker::class,
                [
                    'language' => $appLanguage,
                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                    'pluginOptions' => [
                        'endDate' => '-1d',
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy',
                    ]
                ]
            )
                ->label('Дата начала'); ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($model, 'timeStop')->widget(
                DatePicker::class,
                [
                    'language' => $appLanguage,
                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                    'pluginOptions' => [
                        'endDate' => '-1d',
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy',
                    ]
                ]
            )
                ->label('Дата конца'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?= Html::submitButton(
                '<i class="fa fa-check" aria-hidden="true"></i> Сформировать',
                ['class' => 'btn btn-success']
            ); ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <?= GridView::widget([
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
                                $manage_ac = ApplicationType::find()
                                    ->select('name')
                                    ->leftJoin('{{%moderate_admission_campaign}}', 'application_type.id = moderate_admission_campaign.application_type_id')
                                    ->where(['rbac_auth_assignment_user_id' => $data['user_id']])
                                    ->orderBy('application_type.id')
                                    ->asArray()
                                    ->all();

                                $array_manger_ac = [];
                                foreach ($manage_ac as $m) {
                                    $array_manger_ac[] = $m['name'];
                                }

                                if (empty($array_manger_ac)) {
                                    return 'Для данного модератора нет приемных кампаний';
                                } else {
                                    return join(', ', $array_manger_ac);
                                }
                            }
                        ],
                        [
                            'attribute' => 'all',
                            'label' => 'Общее число обработанных заявлений',
                            'value' => function ($data) {
                                $post = Yii::$app->request->post();
                                $username = $data['username'];
                                $campaign = ArrayHelper::getValue($post, 'DynamicModel.campaign');
                                $timeStop = ArrayHelper::getValue($post, 'DynamicModel.timeStop');
                                $timeStart = ArrayHelper::getValue($post, 'DynamicModel.timeStart');
                                $query = ModerateHistory::getModerateQuery(
                                    $username,
                                    $campaign,
                                    $timeStart,
                                    $timeStop
                                );
                                $query = $query->select(['count' => 'count(application_moderate_history.status)'])->one();
                                $result = ArrayHelper::getValue($query, 'count');
                                if (isset($result)) {
                                    return $result;
                                }
                                return '-';
                            }
                        ],
                        [
                            'attribute' => 'approved',
                            'label' => 'Общее число принятых заявлений',
                            'value' => function ($data) {
                                $post = Yii::$app->request->post();
                                $username = $data['username'];
                                $campaign = ArrayHelper::getValue($post, 'DynamicModel.campaign');
                                $timeStop = ArrayHelper::getValue($post, 'DynamicModel.timeStop');
                                $timeStart = ArrayHelper::getValue($post, 'DynamicModel.timeStart');
                                $query = ModerateHistory::getModerateQuery(
                                    $username,
                                    $campaign,
                                    $timeStart,
                                    $timeStop
                                );
                                $query = $query->select(['count' => 'count(application_moderate_history.status)'])
                                    ->andWhere(['application_moderate_history.status' => BachelorApplication::STATUS_APPROVED])
                                    ->one();
                                $result = ArrayHelper::getValue($query, 'count');
                                if (isset($result)) {
                                    return $result;
                                }
                                return '-';
                            }
                        ],
                        [
                            'attribute' => 'rejected',
                            'label' => 'Общее число отклонённых заявлений',
                            'value' => function ($data) {
                                $post = Yii::$app->request->post();
                                $username = $data['username'];
                                $campaign = ArrayHelper::getValue($post, 'DynamicModel.campaign');
                                $timeStop = ArrayHelper::getValue($post, 'DynamicModel.timeStop');
                                $timeStart = ArrayHelper::getValue($post, 'DynamicModel.timeStart');
                                $query = ModerateHistory::getModerateQuery(
                                    $username,
                                    $campaign,
                                    $timeStart,
                                    $timeStop
                                );
                                $query = $query->select(['count' => 'count(application_moderate_history.status)'])
                                    ->andWhere(['application_moderate_history.status' => BachelorApplication::STATUS_NOT_APPROVED])
                                    ->one();
                                $result = ArrayHelper::getValue($query, 'count');
                                if (isset($result)) {
                                    return $result;
                                }
                                return '-';
                            }
                        ]
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>