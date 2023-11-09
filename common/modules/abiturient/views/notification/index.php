<?php

use common\models\notification\NotificationForm;
use common\models\notification\NotificationType;
use common\models\notification\ReceiverSearch;
use common\models\User;
use common\modules\abiturient\assets\notificationAsset\NotificationAsset;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html as HelpersHtml;
use kartik\select2\Select2;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;








$this->title = Yii::$app->name . ' | ' . Yii::t(
    'notification/index/index',
    'Заголовок страницы рассылки уведомлений: `Рассылка уведомлений`'
);
$appLanguage = Yii::$app->language;
$this->registerCssFile('css/manager_style.css', ['depends' => ['frontend\assets\FrontendAsset']]);
NotificationAsset::register($this);

$data_bool = [
    null => Yii::t(
        'notification/index/filter-block',
        'Текст для значения "Все" в выпадающем списке; блока с фильтрами на стр. рассылки уведомлений: `Все`'
    ),
    1 => Yii::t(
        'notification/index/filter-block',
        'Текст для значения "Есть" в выпадающем списке; блока с фильтрами на стр. рассылки уведомлений: `Есть`'
    ),
    2 => Yii::t(
        'notification/index/filter-block',
        'Текст для значения "Нет" в выпадающем списке; блока с фильтрами на стр. рассылки уведомлений: `Нет`'
    )
];

$select_options = [
    'placeholder' => Yii::t(
        'notification/index/filter-block',
        'Текст для пустого значения выпадающего списка; блока с фильтрами на стр. рассылки уведомлений: `Выберите ...`'
    )
];
?>

<?php Pjax::begin(['timeout' => '5000']) ?>

<?php if (Yii::$app->session->hasFlash('notificationError')) : ?>
    <div class="alert alert-danger">
        <?php foreach (Yii::$app->session->getFlash('notificationError') as $errors) : ?>
            <?php foreach ($errors as $error) : ?>
                <?php echo $error ?><br>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="mx-gutters">
    <h2>
        <a href="<?php echo Url::toRoute(['/sandbox']); ?>" class="btn btn-primary float-right">
            <?= Yii::t(
                'sandbox/moderate/all',
                'Подпись кнопки возвращающей к списку с заявлениями; на стр. рассылки уведомлений: `К списку заявлений поступающих`'
            ) ?>
        </a>

        <?= Yii::t(
            'notification/index/index',
            'Заголовок таблицы с получателями уведомлений; на стр. рассылки уведомлений: `Рассылка уведомлений`'
        ); ?>
    </h2>
</div>

<div class="card-group">
    <div class="card mb-3">
        <div class="card-header">
            <h4>
                <a data-toggle="collapse" href="#collapse-notification">
                    <?php echo Yii::t(
                        'notification/index/filter-block',
                        'Заголовок блока с фильтрами; на стр. рассылки уведомлений: `Фильтры`'
                    ); ?>
                </a>
            </h4>
        </div>

        <div id="collapse-notification" class="panel-collapse collapse in">
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'receiver-search-form',
                    'method' => 'GET',
                    'options' => ['data-pjax' => '1'],
                ]) ?>
                <div class="row notification-filter-container mb-3">
                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'email')
                            ->textInput(['type' => 'email']); ?>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'fio'); ?>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'campaign_code')->widget(Select2::class, [
                            'language' => $appLanguage,
                            'data' => ArrayHelper::merge(
                                [null => Yii::t(
                                    'notification/index/filter-block',
                                    'Текст для значения "Все ПК" в выпадающем списке; блока с фильтрами на стр. рассылки уведомлений: `Все ПК`'
                                )],
                                ArrayHelper::map($listOfAdmissionCampaign, 'reference_uid', 'name')
                            ),
                            'options' => $select_options,
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'has_entrant_tests')->widget(Select2::class, [
                            'language' => $appLanguage,
                            'data' => $data_bool,
                            'options' => $select_options,
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'has_preferences')->widget(Select2::class, [
                            'language' => $appLanguage,
                            'data' => $data_bool,
                            'options' => $select_options,
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'has_target_receptions')->widget(Select2::class, [
                            'language' => $appLanguage,
                            'data' => $data_bool,
                            'options' => $select_options,
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'has_full_cost_recovery')->widget(Select2::class, [
                            'language' => $appLanguage,
                            'data' => $data_bool,
                            'options' => $select_options,
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'application_status')->widget(Select2::class, [
                            'language' => $appLanguage,
                            'data' => ReceiverSearch::getApplicationStatusesData(),
                            'options' => $select_options,
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <?php echo Html::submitButton(Yii::t(
                            'notification/index/filter-block',
                            'Подпись для кнопки применения фильтров; блока с фильтрами на стр. рассылки уведомлений: `Отфильтровать`'
                        ), ['class' => 'btn btn-primary']); ?>
                        <?php echo Html::a(
                            Yii::t(
                                'notification/index/filter-block',
                                'Подпись для кнопки сброса фильтров; блока с фильтрами на стр. рассылки уведомлений: `Сбросить`'
                            ),
                            ['notification/index'],
                            ['class' => 'btn btn-outline-secondary']
                        ); ?>
                    </div>
                </div>

                <?php ActiveForm::end() ?>
            </div>
        </div>
    </div>
</div>

<div class="card-group">
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-9 col-md-8 col-7">
                    <?php echo Select2::widget([
                        'language' => $appLanguage,
                        'name' => 'notification_type',
                        'data' => ArrayHelper::map(NotificationType::find()->enabled()->all(), 'key', 'description'),
                        'options' => ArrayHelper::merge($select_options, [
                            'id' => 'notification-type',
                            'multiple' => true,
                            'placeholder' => Yii::t('notification/index/index', 'Подпись кнопки создания уведомления; на стр. рассылки уведомлений: `Выберите способы доставки`'),
                        ]),
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'maximumInputLength' => 10
                        ],
                    ]); ?>
                </div>

                <div class="col-lg-3 col-md-4 col-5">
                    <?php echo Html::a(
                        Yii::t('notification/index/index', 'Подпись кнопки создания уведомления; на стр. рассылки уведомлений: `Создать уведомления`'),
                        '#',
                        [
                            'class' => 'btn btn-success btn-block',
                            'style' => 'overflow-x: hidden',
                            'data-toggle' => 'modal',
                            'data-target' => '#notification-modal',
                        ]
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <?php echo Yii::t('notification/index/receivers-table', 'Подпись к галочке для выбора всех получателей; на стр. рассылки уведомлений: `Отправить всем`'); ?>
    <?php echo Html::checkbox('send_to_all', false, ['id' => 'send-to-all']); ?>
</div>

<div>
    <?php echo Yii::t('notification/index/receivers-table', 'Подпись к отображаемому числу получателей; на стр. рассылки уведомлений: `Получателей:`') ?>
    <span class="receiver_count">0</span>
</div>

<?php
$query = clone $dataProvider->query;
$this->registerJsVar('all_receivers', $query->select(User::tableName() . '.id')->column());
$this->registerJsVar('no_receivers_text', Yii::t(
    'common/models/notification-form',
    'Текст ошибки от том, что не выбран ни один получатель уведомления формы "Форма уведомления": `Не выбран ни один получатель`'
));
$this->registerJsVar('no_types_text', Yii::t(
    'common/models/notification-form',
    'Текст ошибки от том, что не выбран ни один из способов доставки уведомления формы "Форма уведомления": `Не выбран ни один из способов доставки`'
));
?>

<div class="table-responsive">
    <?php echo GridView::widget([
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm valign-middle'],
        'striped' => false,
        'summary' => false,
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
        'id' => 'receiver-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\CheckboxColumn',
                'cssClass' => 'receiverCheck'
            ],
            [
                'label' => Yii::t('notification/index/receivers-table', 'Подпись колонки для поля "fio" в таблице рассылки уведомлений: `ФИО`'),
                'attribute' => 'abiturientQuestionary.fio'
            ],
            'email',
            [
                'header' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "has_entrant_tests" формы "Поиск получателя": `Наличие экзаменов ВИ`'),
                'attribute' => 'hasEntrantTests',
                'format' => 'boolean',
            ],
            [
                'header' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "has_preferences" формы "Поиск получателя": `Наличие льгот`'),
                'attribute' => 'hasPreferences',
                'format' => 'boolean',
            ],
            [
                'header' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "has_target_receptions" формы "Поиск получателя": `Наличие целевых договоров`'),
                'attribute' => 'hasTargetReceptions',
                'format' => 'boolean',
            ],
            [
                'header' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "has_full_cost_recovery" формы "Поиск получателя": `Наличие направлений с полным возмещением затрат`'),
                'attribute' => 'hasFullCostRecovery',
                'format' => 'boolean',
            ],
            [
                'header' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "application_status" формы "Поиск получателя": `Статус заявления`'),
                'attribute' => 'humanApplicationStatuses',
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => ''
        ],
    ]); ?>
</div>

<?php echo Yii::t(
    'notification/index/receivers-table',
    'Подпись к переключателю количества записей на странице; на стр. рассылки уведомлений: `Показывать на странице`'
); ?>
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
            '<i class="fa fa-arrow-up"></i> ' . Yii::t(
                'notification/index/receivers-table',
                'Подпись кнопки для быстрой прокрутки в начало страницы; на стр. поданных заявлений: `Наверх`'
            ),
            [
                'id' => 'btn_to_up_scroll',
                'onclick' => 'window.toTop()',
                'class' => 'btn btn-warning pull-right',
            ]
        ) ?>
    </div>
</div>

<?php Pjax::end(); ?>

<?php Modal::begin([
    'title' => Html::tag('h4', Yii::t(
        'notification/index/index',
        'Заголовок модального окна для создания уведомления на странице рассылки уведомлений: `Новое уведомление`'
    )),
    'size' => 'modal-lg',
    'id' => "notification-modal",
    'options' => [
        'tabindex' => false,
    ],
]);

echo $this->render('_form', [
    'model' => new NotificationForm()
]);

Modal::end();
