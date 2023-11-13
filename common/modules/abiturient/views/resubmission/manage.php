<?php

use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html as HelpersHtml;
use kartik\select2\Select2;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;








\common\assets\ResubmissionManagementAsset::register($this);

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'resubmission/manage',
    'Заголовок страницы управления повторной подачей заявлений: `Управление повторной подачей заявлений`'
);
$appLanguage = Yii::$app->language;
$this->registerCssFile('css/manager_style.css', ['depends' => ['frontend\assets\FrontendAsset']]);

$select_options = [
    'placeholder' => Yii::t(
        'resubmission/manage/filter-block',
        'Текст для пустого значения выпадающего списка; блока с фильтрами на стр. рассылки уведомлений: `Выберите ...`'
    )
];
?>

<?php Pjax::begin([
    'options' => ['id' => 'resubmission_management_users_table_pjax'],
    'timeout' => '5000'
]) ?>

<div class="mx-gutters">
    <h2>
        <a href="<?php echo Url::toRoute(['/sandbox']); ?>" class="btn btn-primary float-right">
            <?= Yii::t(
                'sandbox/moderate/all',
                'Подпись кнопки возвращающей к списку с заявлениями: `К списку заявлений поступающих`'
            ) ?>
        </a>

        <?= Yii::t(
            'resubmission/manage/index',
            'Заголовок таблицы с пользователями для управления их повторной подачей: `Управление повторной подачей заявлений`'
        ); ?>
    </h2>
</div>

<div class="card-group">
    <div class="card mb-3">
        <div class="card-header">
            <h4>
                <a data-toggle="collapse" href="#collapse-manage">
                    <?php echo Yii::t(
                        'resubmission/manage/filter-block',
                        'Заголовок блока с фильтрами; на стр. рассылки уведомлений: `Фильтры`'
                    ); ?>
                </a>
            </h4>
        </div>

        <div id="collapse-manage" class="panel-collapse collapse in">
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
                        <?php echo $form->field($searchModel, 'campaign_ref_uid')->widget(Select2::class, [
                            'language' => $appLanguage,
                            'data' => ArrayHelper::merge(
                                [null => Yii::t(
                                    'resubmission/manage/filter-block',
                                    'Текст для значения "Все ПК" в выпадающем списке; блока с фильтрами на стр. рассылки уведомлений: `Все ПК`'
                                )],
                                ArrayHelper::map($listOfAdmissionCampaign, 'reference_uid', 'name')
                            ),
                            'options' => $select_options,
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <?php echo $form->field($searchModel, 'allow_resubmit')->widget(Select2::class, [
                            'language' => $appLanguage,
                            'data' => [
                                null => Yii::t(
                                    'resubmission/manage/filter-block',
                                    'Текст для значения "Все" в выпадающем списке; блока с фильтрами на стр. рассылки уведомлений: `Все`'
                                ),
                                1 => Yii::t(
                                    'resubmission/manage/filter-block',
                                    'Текст для значения "Да" в выпадающем списке; блока с фильтрами на стр. рассылки уведомлений: `Да`'
                                ),
                                0 => Yii::t(
                                    'resubmission/manage/filter-block',
                                    'Текст для значения "Нет" в выпадающем списке; блока с фильтрами на стр. рассылки уведомлений: `Нет`'
                                ),
                            ],
                            'options' => $select_options,
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <?php echo Html::submitButton(Yii::t(
                            'resubmission/manage/filter-block',
                            'Подпись для кнопки применения фильтров; блока с фильтрами на стр. управления повторной подачей заявлений: `Отфильтровать`'
                        ), ['class' => 'btn btn-primary']); ?>
                        <?php echo Html::a(
                            Yii::t(
                                'resubmission/manage/filter-block',
                                'Подпись для кнопки сброса фильтров; блока с фильтрами на стр. управления повторной подачей заявлений: `Сбросить`'
                            ),
                            ['resubmission/manage'],
                            ['class' => 'btn btn-outline-secondary']
                        ); ?>
                    </div>
                </div>

                <?php ActiveForm::end() ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <?php echo Html::button(
            Yii::t(
                'resubmission/manage',
                'Подпись для кнопки разрешения повторной подачи: `Разрешить выделенным пользователям повторную подачу заявления`'
            ),
            ['class' => 'btn btn-primary', 'id' => 'allow_resubmit']
        ) ?>
        <?php echo Html::button(
            Yii::t(
                'resubmission/manage',
                'Подпись для кнопки запрета повторной подачи: `Запретить выделенным пользователям повторную подачу заявления`'
            ),
            ['class' => 'btn btn-primary', 'id' => 'disallow_resubmit']
        ) ?>
    </div>
</div>
<div class="table-responsive">
    <?php echo GridView::widget([
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm valign-middle'],
        'options' => ['id' => 'resubmission_management_users_table'],
        'rowOptions' => function ($model, $key, $index, $grid) {
            return ['data' => ['key' => json_encode([
                'user_id' => $model['user_id'],
                'type_id' => $model['type_id']
            ])]];
        },
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
            ],
            [
                'attribute' => 'fio',
                'label' => Yii::t('resubmission/manage', 'Подпись для колонки с ФИО в таблице управления повторной подачей заявлений: `ФИО`'),
            ],
            [
                'attribute' => 'email',
                'label' => Yii::t('resubmission/manage', 'Подпись для колонки с email в таблице управления повторной подачей заявлений: `Email`'),
            ],
            [
                'attribute' => 'campaign_name',
                'label' => Yii::t('resubmission/manage', 'Подпись для колонки с ПК в таблице управления повторной подачей заявлений: `Приёмная кампания`'),
            ],
            [
                'attribute' => 'allow_resubmit',
                'label' => Yii::t('resubmission/manage', 'Подпись для колонки с правом повторной подачи в таблице управления повторной подачей заявлений: `Повторная подача разрешена`'),
                'value' => function ($model) {
                    return $model['allow_resubmit'] ? Yii::t('resubmission/manage', 'Да') : Yii::t('resubmission/manage', 'Нет');
                },
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => ''
        ],
    ]); ?>
</div>

<?php echo Yii::t(
    'resubmission/manage/receivers-table',
    'Подпись к переключателю количества записей на странице; на стр. управления повторной подачей заявлений: `Показывать на странице`'
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
                'resubmission/manage/receivers-table',
                'Подпись кнопки для быстрой прокрутки в начало страницы: `Наверх`'
            ),
            [
                'id' => 'btn_to_up_scroll',
                'onclick' => 'window.toTop()',
                'class' => 'btn btn-warning pull-right',
            ]
        ) ?>
    </div>
</div>

<?php Pjax::end();
