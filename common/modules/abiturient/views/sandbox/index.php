<?php

use backend\models\RBACAuthAssignment;
use common\assets\ChangeGridViewPaginationAsset;
use common\components\PhoneWidget\PhoneWidget;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\Country;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\ApplicationSearch;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html as HelpersHtml;
use kartik\select2\Select2;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\MaskedInput;







$this->title = Yii::$app->name . ' | ' . Yii::t(
    'sandbox/index/all',
    'Заголовок страницы поданных заявлений: `Проверка заявлений`'
);

$appLanguage = Yii::$app->language;

ChangeGridViewPaginationAsset::register($this);

$this->registerCssFile('css/manager_style.css', ['depends' => ['frontend\assets\FrontendAsset']]);

$timeZoneError = false;
$timeZoneLocal = date_default_timezone_get();
$timeZoneGlobal = ini_get('date.timezone');
if (strcmp($timeZoneLocal, $timeZoneGlobal) || strlen((string)$timeZoneGlobal) < 1) :
    echo Html::tag(
        'div',
        Yii::t(
            'sandbox/index/all',
            'Текст ошибки о не корректно настроенном часовом поясе; на стр. поданных заявлений: `<strong>В портале не установлен часовой пояс.</strong> Вы не сможете работать с порталом до того, как проблема будет решена. Приносим извинения за неудобства. Обратитесь к администратору портала.`'
        ),
        ['class' => 'alert alert-danger']
    );
else : ?>
    <div class="row">
        <div class="col-12">
            <?php if (Yii::$app->user->identity->hasCampaignsToModerateWithRestrictedResubmission()) : ?>
                <a class="btn btn-success float-right ml-1" href="<?php echo Url::to(['/resubmission/manage']) ?>">
                    <?php echo Yii::t(
                        'sandbox/index/all',
                        'Подпись кнопки перехода к странице управление повторной подачей; на стр. поданных заявлений: `Управление повторной подачей заявлений`'
                    ); ?>
                </a>
            <?php endif; ?>

            <?= $this->render('partial/_notification_and_chat_btns') ?>

            <h2>
                <?= Yii::t(
                    'sandbox/index/all',
                    'Заголовок таблицы с заявлениями; на стр. поданных заявлений: `Заявления`'
                ); ?>
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="nav-item <?php if ($type == "moderate") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "moderate") : ?>#moderate<?php else : ?><?= Url::toRoute("sandbox/index"); ?><?php endif; ?>" aria-controls="moderate">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с требующими проверки заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Требуют проверки`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == "approved") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "approved") : ?>#approved<?php else : ?><?= Url::toRoute("sandbox/approved"); ?><?php endif; ?>" aria-controls="approved">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с принятыми заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Принятые`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == 'enlisted') : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == 'enlisted') : ?>#enlisted<?php else : ?><?= Url::toRoute('sandbox/enlisted'); ?><?php endif; ?>" aria-controls="enlisted">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с зачисленными заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Зачисленные`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == "declined") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "declined") : ?>#declined<?php else : ?><?= Url::toRoute("sandbox/declined"); ?><?php endif; ?>" aria-controls="declined">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с отклонёнными заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Отклонённые`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == "want-delete") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "want-delete") : ?>#want-delete<?php else : ?><?= Url::toRoute("sandbox/want-delete"); ?><?php endif; ?>" aria-controls="want-delete">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с заявлениями поданные на отзыв; в таблице с заявлениями на стр. поданных заявлений: `Подан отзыв`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == "deleted") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "deleted") : ?>#deleted<?php else : ?><?= Url::toRoute("sandbox/deleted"); ?><?php endif; ?>" aria-controls="deleted">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с отозванными заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Отозванные`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == "preparing") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "preparing") : ?>#preparing<?php else : ?><?= Url::toRoute("sandbox/preparing"); ?><?php endif; ?>" aria-controls="preparing">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с не подданными заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Готовятся`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == "questionaries") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "questionaries") : ?>#questionaries<?php else : ?><?= Url::toRoute("sandbox/questionaries"); ?><?php endif; ?>" aria-controls="questionaries">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с анкетами без заявлений; в таблице с заявлениями на стр. поданных заявлений: `Анкеты без заявлений`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == "enrollment-rejection") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "enrollment-rejection") : ?>#enrollment-rejection<?php else : ?><?= Url::toRoute("sandbox/enrollment-rejection"); ?><?php endif; ?>" aria-controls="enrollment-rejection">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с заявлениями с поданным отказом от зачисления; в таблице с заявлениями на стр. поданных заявлений: `Отказ от зачисления`'
                        ); ?>
                    </a>
                </li>

                <li role="presentation" class="nav-item <?php if ($type == "all") : ?>active<?php endif; ?>">
                    <a class="nav-link" href="<?php if ($type == "all") : ?>#all<?php else : ?><?= Url::toRoute("sandbox/all"); ?><?php endif; ?>" aria-controls="declined">
                        <?= Yii::t(
                            'sandbox/index/all',
                            'Подпись вкладки с всеми заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Все`'
                        ); ?>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" role="tabpanel" id="<?= $type; ?>">
                    <?php if (empty($listOfAdmissionCampaign)) {
                        $emptyText = Html::tag(
                            'div',
                            Yii::t(
                                'sandbox/index/all',
                                'Сообщение об ошибке доступа; на стр. поданных заявлений: `У {username} нет доступа к приемным кампаниям. Обратитесь к администратору.`',
                                ['username' => $currentUser->username]
                            ),
                            [
                                'class' => 'alert alert-danger',
                                'role' => 'alert'
                            ]
                        );
                    } else {
                        $emptyText = '<div class="alert alert-info" role="alert">';
                        if ($type == 'moderate') {
                            $emptyText .= Yii::t(
                                'sandbox/index/all',
                                'Сообщении об отсутствии данных для вкладки с требующими проверки заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Нет заявлений, требующих проверки.`'
                            );
                        } elseif ($type == 'approved') {
                            $emptyText .= Yii::t(
                                'sandbox/index/all',
                                'Сообщении об отсутствии данных для вкладки с принятыми заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Нет принятых заявлений.`'
                            );
                        } elseif ($type == 'enlisted') {
                            $emptyText .= Yii::t(
                                'sandbox/index/all',
                                'Сообщении об отсутствии данных для вкладки с зачисленными заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Нет зачисленных заявлений.`'
                            );
                        } elseif ($type == 'declined') {
                            $emptyText .= Yii::t(
                                'sandbox/index/all',
                                'Сообщении об отсутствии данных для вкладки с отклонёнными заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Нет отклонённых заявлений.`'
                            );
                        } elseif ($type == 'want-delete') {
                            $emptyText .= Yii::t(
                                'sandbox/index/all',
                                'Сообщении об отсутствии данных для вкладки с заявлениями поданные на отзыв; в таблице с заявлениями на стр. поданных заявлений: `Нет заявлений помеченных на отзыв.`'
                            );
                        } elseif ($type == 'deleted') {
                            $emptyText .= Yii::t(
                                'sandbox/index/all',
                                'Сообщении об отсутствии данных для вкладки с отозванными заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Нет отозванных заявлений.`'
                            );
                        } elseif ($type == 'preparing') {
                            $emptyText .= Yii::t(
                                'sandbox/index/all',
                                'Сообщении об отсутствии данных для вкладки с не поданными заявлениями; в таблице с заявлениями на стр. поданных заявлений: `Нет не поданных заявлений.`'
                            );
                        } elseif ($type == 'enrollment-rejection') {
                            $emptyText .= Yii::t(
                                'sandbox/index/all',
                                'Сообщении об отсутствии данных для вкладки с поданными отказами от зачисления; в таблице с заявлениями на стр. поданных заявлений: `Нет поданных отказов от зачисления.`'
                            );
                        }
                        $emptyText .= '</div>';
                    } ?>

                    <?php if ($searchModel->filters) : ?>
                        <div class="card-group">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4>
                                        <a data-toggle="collapse" href="#collapse-moderate">
                                            <?= Yii::t(
                                                'sandbox/index/filter-block',
                                                'Заголовок блока с фильтрами; на стр. поданных заявлений: `Фильтры`'
                                            ); ?>
                                        </a>
                                    </h4>
                                </div>

                                <div id="collapse-moderate" class="panel-collapse collapse<?= $searchModel->filterLoaded ? ' in' : '' ?>">
                                    <div class="card-body">
                                        <?php $form = ActiveForm::begin([
                                            'id' => 'login-form',
                                            'action' => Url::toRoute(['sandbox/' . ($type === 'moderate' ? 'index' : $type)]),
                                            'method' => 'GET', 
                                        ]) ?>

                                        <?php $filtersGrid = ''; ?>
                                        <?php foreach ($searchModel->filters as $filter) : ?>
                                            <?php $options = ['data-placement' => 'top'];

                                            switch ($filter->name) {
                                                case 'status':
                                                case 'agreement':
                                                case 'preferences':
                                                case 'statusBlock':
                                                case 'campaign_code':
                                                case 'educationForm':
                                                case 'targetReception':
                                                case 'review_agreement':
                                                case 'hasIndividualAchievement':
                                                case 'financial_basis':
                                                case 'education_level':
                                                case 'admission_category':
                                                    $options['placeholder'] = Yii::t(
                                                        'sandbox/index/filter-block',
                                                        'Текст для пустого значения выпадающего списка; блока с фильтрами на стр. поданных заявлений: `Выберите ...`'
                                                    );
                                                    $options['class'] = 'col-12 form-control small_font';
                                                    $data = [];

                                                    switch ($filter->name) {
                                                        case 'statusBlock':
                                                            $data = BachelorApplication::getBlockStatusAliasList();
                                                            break;

                                                        case 'educationForm':
                                                            $data = ArrayHelper::merge(
                                                                ['' => ''],
                                                                ArrayHelper::map(
                                                                    Speciality::find()
                                                                        ->select(['eduform_name', 'education_form_ref.reference_uid', 'education_form_ref_id'])
                                                                        ->joinWith('educationFormRef education_form_ref')
                                                                        ->groupBy(['eduform_name', 'education_form_ref.reference_uid', 'education_form_ref_id'])
                                                                        ->where([Speciality::tableName() . '.archive' => false])
                                                                        ->all(),
                                                                    'educationFormRef.reference_uid',
                                                                    'eduform_name'
                                                                )
                                                            );
                                                            break;

                                                        case 'campaign_code':
                                                            $data = ArrayHelper::merge(
                                                                [null => Yii::t(
                                                                    'sandbox/index/filter-block',
                                                                    'Текст для значения "Все ПК" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Все ПК`'
                                                                )],
                                                                ArrayHelper::map($listOfAdmissionCampaign, 'reference_uid', 'name')
                                                            );
                                                            break;

                                                        case 'status':
                                                            $data = BachelorApplication::sandboxMessages();
                                                            break;

                                                        case 'agreement':
                                                        case 'preferences':
                                                        case 'targetReception':
                                                        case 'review_agreement':
                                                        case 'hasIndividualAchievement':
                                                            $data = [
                                                                null => Yii::t(
                                                                    'sandbox/index/filter-block',
                                                                    'Текст для значения "Все" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Все`'
                                                                ),
                                                                1 => Yii::t(
                                                                    'sandbox/index/filter-block',
                                                                    'Текст для значения "Есть" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Есть`'
                                                                ),
                                                                2 => Yii::t(
                                                                    'sandbox/index/filter-block',
                                                                    'Текст для значения "Нет" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Нет`'
                                                                )
                                                            ];
                                                            break;

                                                        case 'financial_basis':
                                                            $data = ArrayHelper::merge(
                                                                ['' => ''],
                                                                ArrayHelper::map(
                                                                    Speciality::find()
                                                                        ->select(['finance_name', 'education_source_ref.reference_uid', 'education_source_ref_id'])
                                                                        ->joinWith('educationSourceRef education_source_ref')
                                                                        ->groupBy(['finance_name', 'education_source_ref.reference_uid', 'education_source_ref_id'])
                                                                        ->where([Speciality::tableName() . '.archive' => false])
                                                                        ->all(),
                                                                    'educationSourceRef.reference_uid',
                                                                    'finance_name'
                                                                )
                                                            );
                                                            break;

                                                        case 'education_level':
                                                            $data = ArrayHelper::merge(
                                                                ['' => ''],
                                                                ArrayHelper::map(
                                                                    Speciality::find()
                                                                        ->select(['edulevel_name', 'education_level_ref.reference_uid', 'education_level_ref_id'])
                                                                        ->joinWith('educationLevelRef education_level_ref')
                                                                        ->groupBy(['edulevel_name', 'education_level_ref.reference_uid', 'education_level_ref_id'])
                                                                        ->where([Speciality::tableName() . '.archive' => false])
                                                                        ->all(),
                                                                    'educationLevelRef.reference_uid',
                                                                    'edulevel_name'
                                                                )
                                                            );
                                                            break;

                                                        case 'admission_category':
                                                            $data = ArrayHelper::merge(
                                                                ['' => ''],
                                                                ArrayHelper::map(
                                                                    AdmissionCategory::find()->notMarkedToDelete()->active()->all(),
                                                                    'ref_key',
                                                                    'description'
                                                                )
                                                            );
                                                            break;
                                                    }

                                                    $field = $form->field($searchModel, $filter->name)
                                                        ->widget(Select2::class, [
                                                            'language' => $appLanguage,
                                                            'data' => $data,
                                                            'options' => $options,
                                                            'pluginOptions' => ['allowClear' => true],
                                                        ]);
                                                    break;

                                                case 'citizenship':
                                                case 'historyChanges':
                                                case 'specialityName':
                                                case 'lastManagerName':
                                                    $options['multiple'] = true;
                                                    $options['placeholder'] = Yii::t(
                                                        'sandbox/index/filter-block',
                                                        'Текст для пустого значения выпадающего списка; блока с фильтрами на стр. поданных заявлений: `Выберите ...`'
                                                    );
                                                    $options['class'] = 'col-12 form-control small_font';
                                                    $data = [];

                                                    switch ($filter->name) {
                                                        case 'specialityName':
                                                            $firstQuery = Speciality::find()
                                                                ->select(['speciality_name', 'directionRef.reference_uid', 'direction_ref_id'])
                                                                ->joinWith('directionRef directionRef')
                                                                ->leftJoin(
                                                                    'bachelor_speciality',
                                                                    'dictionary_speciality.id = bachelor_speciality.speciality_id'
                                                                )
                                                                ->where(['dictionary_speciality.archive' => false])
                                                                ->andWhere(['bachelor_speciality.archive' => false])
                                                                ->andWhere([
                                                                    'in',
                                                                    'bachelor_speciality.application_id',
                                                                    (clone $applications->query)->select('bachelor_application.id')
                                                                ])
                                                                ->groupBy(['speciality_name', 'directionRef.reference_uid', 'direction_ref_id']);
                                                            if (isset($searchModel->{$filter->name}) && $searchModel->{$filter->name}) {
                                                                $additionalQuery = Speciality::find()
                                                                    ->alias('additional_dictionary_spec')
                                                                    ->select(['speciality_name', 'directionRef.reference_uid', 'direction_ref_id'])
                                                                    ->joinWith('directionRef directionRef')
                                                                    ->where(['additional_dictionary_spec.archive' => false])
                                                                    ->andWhere([
                                                                        'in',
                                                                        'directionRef.reference_uid',
                                                                        $searchModel->{$filter->name}
                                                                    ]);
                                                                $firstQuery->union($additionalQuery);
                                                            }

                                                            $data = ArrayHelper::map(
                                                                $firstQuery->all(),
                                                                'directionRef.reference_uid',
                                                                'speciality_name'
                                                            );
                                                            break;

                                                        case 'historyChanges':
                                                            $data = ApplicationHistory::historyTypeNames();
                                                            break;

                                                        case 'citizenship':
                                                            $data = ArrayHelper::merge(
                                                                [null => Yii::t(
                                                                    'sandbox/index/filter-block',
                                                                    'Текст для значения "Все" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Все`'
                                                                )],
                                                                ArrayHelper::map(
                                                                    Country::find()->active()->orderBy('name')->all(),
                                                                    'id',
                                                                    'name'
                                                                )
                                                            );
                                                            break;

                                                        case 'lastManagerName':
                                                            $userTableName = User::tableName();
                                                            $rbacAuthAssignmentTableName = RBACAuthAssignment::tableName();

                                                            $data = ArrayHelper::map(
                                                                (new Query())
                                                                    ->select("{$userTableName}.username, {$userTableName}.id")
                                                                    ->from($userTableName)
                                                                    ->leftJoin($rbacAuthAssignmentTableName, "{$userTableName}.id = {$rbacAuthAssignmentTableName}.user_id")
                                                                    ->andWhere(["{$rbacAuthAssignmentTableName}.item_name" => 'manager'])
                                                                    ->all(),
                                                                'id',
                                                                'username'
                                                            );
                                                            break;

                                                        
                                                    }

                                                    $field = $form->field($searchModel, $filter->name)
                                                        ->widget(Select2::class, [
                                                            'language' => $appLanguage,
                                                            'value' => $searchModel->{$filter->name},
                                                            'data' => $data,
                                                            'options' => $options,
                                                            'pluginOptions' => [
                                                                'tags' => true,
                                                                'tokenSeparators' => [',', ' '],
                                                                'maximumInputLength' => 10
                                                            ],
                                                        ]);
                                                    break;

                                                case 'sent_at':
                                                case 'birthday':
                                                case 'created_at':
                                                case 'last_management_at':
                                                    $field = $form->field($searchModel, $filter->name)
                                                        ->widget(DatePicker::class, [
                                                            'language' => $appLanguage,
                                                            'separator' => Yii::t(
                                                                'sandbox/index/filter-block',
                                                                'Текст для междометия дат стоящим между дат начала и конца; блока с фильтрами на стр. поданных заявлений: `по`'
                                                            ),
                                                            'name2' => "to_{$filter->name}",
                                                            'attribute2' => "to_{$filter->name}",
                                                            'type' => DatePicker::TYPE_RANGE,
                                                            'options' => array_merge(
                                                                ['placeholder' => Yii::t(
                                                                    'sandbox/index/filter-block',
                                                                    'Текст для пустого значения даты начала; блока с фильтрами на стр. поданных заявлений: `Начало`'
                                                                )],
                                                                $options
                                                            ),
                                                            'options2' => array_merge(
                                                                ['placeholder' => Yii::t(
                                                                    'sandbox/index/filter-block',
                                                                    'Текст для пустого значения даты окончания; блока с фильтрами на стр. поданных заявлений: `Конец`'
                                                                )],
                                                                $options
                                                            ),
                                                            'pluginOptions' => [
                                                                'autoclose' => false,
                                                                'format' => 'dd.mm.yyyy',
                                                            ]
                                                        ]);
                                                    break;

                                                case 'exam_form': 
                                                    $data = ArrayHelper::map(
                                                        StoredDisciplineFormReferenceType::findAll(['archive' => false]),
                                                        'reference_uid',
                                                        'reference_name'
                                                    );

                                                    
                                                    $examCode = Yii::$app->configurationManager->getCode('discipline_exam_form');
                                                    if (isset($data[$examCode])) {
                                                        $data = [$examCode => $data[$examCode]] + $data;
                                                    }
                                                    $egeCode = Yii::$app->configurationManager->getCode('discipline_ege_form');
                                                    if (isset($data[$egeCode])) {
                                                        $data = [$egeCode => $data[$egeCode]] + $data;
                                                    }

                                                    $field = $form->field($searchModel, $filter->name)->widget(Select2::class, [
                                                        'data' => $data,
                                                        'options' => [
                                                            'data-placement' => 'top',
                                                            'placeholder' => Yii::t(
                                                                'sandbox/index/filter-block',
                                                                'Текст для пустого значения выпадающего списка; блока с фильтрами на стр. поданных заявлений: `Выберите ...`'
                                                            ),
                                                            'class' => 'col-12 form-control small_font',
                                                        ],
                                                        'pluginOptions' => ['allowClear' => true],
                                                    ]);
                                                    break;
                                                case 'phone_number':
                                                    $field = $form->field($searchModel, $filter->name)->widget(MaskedInput::class, [
                                                        'mask' => [PhoneWidget::$phoneNumberMask],
                                                        'clientOptions' => ['clearMaskOnLostFocus' => true, 'greedy' => false],
                                                        'options' => [
                                                            'placeholder' => '+7(999)9999999',
                                                            'data-mask' => PhoneWidget::$phoneNumberMask,
                                                            'class' => 'col-12 form-control small_font',
                                                        ]
                                                    ]);
                                                    break;
                                                case 'snils':
                                                    $field = $form->field($searchModel, $filter->name)->widget(MaskedInput::class, [
                                                        'mask' => '999-999-999 99',
                                                        'clientOptions' => ['clearMaskOnLostFocus' => false],
                                                        'options' => [
                                                            'class' => 'col-12 form-control small_font',
                                                        ],
                                                    ]);
                                                    break;

                                                default:
                                                    $field = $form->field($searchModel, $filter->name)
                                                        ->textInput($options);
                                                    break;
                                            } ?>

                                            <?php $filtersGrid .= Html::tag(
                                                'div',
                                                $field->label(Yii::t(
                                                    'abiturient/filter-table',
                                                    $filter->label
                                                )),
                                                ['class' => 'col-xl-3 col-lg-4 col-md-6 col-12']
                                            ) ?>
                                        <?php endforeach; ?>
                                        <div class="row mb-3">
                                            <?= $filtersGrid ?>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <?= Html::submitButton(
                                                    Yii::t(
                                                        'sandbox/index/filter-block',
                                                        'Подпись для кнопки применения фильтров; блока с фильтрами на стр. поданных заявлений: `Отфильтровать`'
                                                    ),
                                                    ['class' => 'btn btn-primary']
                                                ) ?>
                                                <?php if ($searchModel->filterLoaded) {
                                                    $resetType = ('moderate' === $type) ? 'index' : $type;
                                                    echo Html::a(
                                                        Yii::t(
                                                            'sandbox/index/filter-block',
                                                            'Подпись для кнопки сброса фильтров; блока с фильтрами на стр. поданных заявлений: `Сбросить`'
                                                        ),
                                                        ['sandbox/reset-filters', 'type' => $resetType],
                                                        ['class' => 'btn btn-outline-secondary']
                                                    );
                                                } ?>
                                            </div>
                                        </div>
                                        <?php ActiveForm::end() ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?= GridView::widget([
                        'hover' => true,
                        'headerContainer' => ['class' => 'thead-light'],
                        'tableOptions' => ['class' => 'table-sm valign-middle'],
                        'striped' => false,
                        'summary' => false,
                        'emptyText' => $emptyText,
                        'dataProvider' => $applications,
                        'rowOptions' => function ($model) use ($type) {
                            

                            if (
                                $type != 'want-delete' &&
                                $model->status == BachelorApplication::STATUS_WANTS_TO_RETURN_ALL
                            ) {
                                return ['class' => 'table-danger'];
                            }

                            $tnBachelorApplication = BachelorApplication::tableName();
                            $hasAdmissionAgreement = BachelorApplication::hasAdmissionAgreementQuery(
                                BachelorApplication::find()
                                    ->andWhere(["{$tnBachelorApplication}.id" => $model->id])
                            )->exists();
                            if ($hasAdmissionAgreement) {
                                return ['class' => 'table-info'];
                            }
                            return [];
                        },
                        'pager' => [
                            'firstPageLabel' => '<<',
                            'prevPageLabel' => '<',
                            'nextPageLabel' => '>',
                            'lastPageLabel' => '>>',
                        ],
                        'columns' => $searchModel->getColumnsLayout($type),
                        'formatter' => [
                            'class' => 'yii\i18n\Formatter',
                            'nullDisplay' => ''
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <?= Yii::t(
                'sandbox/index/all',
                'Подпись к переключателю количества записей на странице; на стр. поданных заявлений: `Показывать на странице`'
            ); ?>
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
                '<i class="fa fa-arrow-up"></i> ' . Yii::t(
                    'sandbox/index/all',
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
<?php endif;