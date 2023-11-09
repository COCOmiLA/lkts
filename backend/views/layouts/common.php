<?php

use backend\models\DictionaryUpdateHistory;
use backend\models\SystemLog;
use backend\models\SystemLogInfo;
use backend\widgets\Menu;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\ChecksumManager\ChecksumManager;
use common\components\EnvironmentManager\EnvironmentManager;
use common\models\TimelineEvent;
use yii\bootstrap4\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;





$navBg = Yii::$app->keyStorage->get('backend.theme-skin', 'navbar-dark bg-lightblue');
$logoBg = Yii::$app->keyStorage->get('backend.logo-skin', 'bg-lightblue');

?>

<?php $this->beginContent('@backend/views/layouts/base.php'); ?>

<div class="wrapper">
    <nav class="main-header navbar navbar-expand <?= $navBg ?>">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fa fa-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item" id="timeline-notifications">
                <a class="nav-link" href="<?= Url::to(['/timeline-event/index']) ?>" aria-expanded="false">
                    <i class="fa fa-bell"></i>

                    <span class="badge badge-success">
                        <?= TimelineEvent::find()->today()->count() ?>
                    </span>
                </a>
            </li>

            <li class="nav-item dropdown" id="timeline-errors">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="fa fa-warning"></i>

                    <span class="badge badge-danger">
                        <?= SystemLog::getCount() ?>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        <?= Yii::t('backend', 'У вас {num} записей в журнале', ['num' => SystemLog::getCount()]) ?>
                    </span>

                    <!-- записи подгружаются через ajax-->
                    <?= Html::a(
                        Yii::t('backend', 'Смотреть всё'),
                        ['/log/index'],
                        ['class' => 'dropdown-item dropdown-footer']
                    ) ?>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="fa fa-info-circle"></i>

                    <span class="badge badge-info">
                        <?= SystemLogInfo::find()->count() ?>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        <?= Yii::t('backend', 'У вас {num} записей в журнале', ['num' => SystemLogInfo::find()->count()]) ?>
                    </span>

                    <div class="dropdown-divider"></div>

                    <?php foreach (SystemLogInfo::find()->orderBy(['log_time' => SORT_DESC])->limit(5)->all() as $logEntry) : ?>
                        <a class="dropdown-item" href="<?= Yii::$app->urlManager->createUrl(['/log/v', 'id' => $logEntry->id]) ?>">
                            <i class="fa fa-info-circle text-blue"></i>
                            <?= $logEntry->category ?>
                        </a>

                        <div class="dropdown-divider"></div>
                    <?php endforeach; ?>

                    <?= Html::a(
                        Yii::t('backend', 'Смотреть всё'),
                        ['/log/info'],
                        ['class' => 'dropdown-item dropdown-footer']
                    ) ?>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                    <?= Yii::$app->user->identity->username ?>

                    <i class="caret"></i>
                </a>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        <strong>
                            <?= Yii::$app->user->identity->username ?>
                        </strong>

                        <br>

                        <small>
                            <?= Yii::t('backend', 'Member since {0, date, short}', Yii::$app->user->identity->created_at) ?>
                        </small>
                    </span>

                    <div class="dropdown-divider"></div>

                    <div class="dropdown-item dropdown-footer">
                        <?= Html::a(
                            Yii::t('backend', 'Профиль'),
                            ['/sign-in/profile'],
                            ['class' => 'btn btn-secondary btn-flat']
                        ) ?>

                        <?= Html::a(
                            Yii::t('backend', 'Аккаунт'),
                            ['/sign-in/account'],
                            ['class' => 'btn btn-secondary btn-flat']
                        ) ?>


                        <?= Html::a(
                            Yii::t('backend', 'Выход'),
                            ['/sign-in/logout'],
                            ['class' => 'btn btn-success btn-flat', 'data-method' => 'post']
                        ) ?>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <?= Html::a('<i class="fa fa-cogs ml-0"></i>', ['/site/settings'], ['class' => 'nav-link']) ?>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a class="brand-link <?= $logoBg ?>" href="<?= Yii::getAlias('@frontendUrl') ?>">
            <?= Yii::$app->name ?>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a class="d-block" href="#">
                        <strong>
                            <?= Yii::t('backend', 'Привет, {username}', ['username' => Yii::$app->user->identity->getPublicIdentity()]) ?>
                        </strong>
                    </a>

                    <a class="d-block" href="<?= Url::to(['/sign-in/profile']) ?>">
                        <i class="fa fa-circle text-success"></i>

                        <?= Yii::$app->formatter->asDatetime(time()) ?>
                    </a>
                </div>
            </div>

            <nav class="mt-2">
                <?php
                $pathToSvg = FileHelper::normalizePath('/frontend/web/install/img/RecaptchaLogo.svg');
                $items = [
                    [
                        'label' => 'Информация',
                        'options' => ['class' => 'nav-header']
                    ],
                    [
                        'label' => Yii::t('backend', 'Хроника'),
                        'icon' => '<i class="align-self-center nav-icon fa fa-bar-chart"></i>',
                        'url' => ['/timeline-event/index'],
                        'badge' => TimelineEvent::find()->today()->count(),
                        'badgeBgClass' => 'badge-success d-flex align-items-center',
                    ],
                    [
                        'label' => 'Отчеты',
                        'url' => '#',
                        'icon' => '<i class="align-self-center nav-icon fa fa-bars"></i>',
                        'items' => [
                            [
                                'label' => 'Сводная статистика',
                                'url' => ['/reports/summary'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'
                            ],
                            [
                                'label' => 'Отчёт по направлениям',
                                'url' => ['/manage/spec-report'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'
                            ],
                            [
                                'label' => 'Отчёт по модераторам',
                                'url' => ['/manage/report'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'
                            ],
                        ]
                    ],
                    [
                        'label' => Yii::t('backend', 'Система'),
                        'options' => ['class' => 'nav-header']
                    ],
                    [
                        'label' => Yii::t('backend', 'Пользователи'),
                        'icon' => '<i class="align-self-center nav-icon fa fa-users"></i>',
                        'url' => ['/user/index'],
                        'visible' => Yii::$app->user->can('administrator')
                    ],
                    [
                        'label' => 'Основное',
                        'url' => '#',
                        'icon' => '<i class="align-self-center nav-icon fa fa-bars"></i>',
                        'items' => [
                            [
                                'label' => Yii::t('backend', 'Журнал ошибок'),
                                'url' => ['/log/index'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'badge' => SystemLog::getCount(),
                                'badgeBgClass' => 'badge-danger d-flex align-items-center',
                            ],
                            [
                                'label' => 'Журнал событий',
                                'url' => ['/log/info'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'badge' => SystemLogInfo::find()->count(),
                                'badgeBgClass' => 'badge-info d-flex align-items-center',
                            ],
                            ['label' => 'Обновление', 'url' => ['/update/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Информация о конфигурации', 'url' => ['/settings/php-info'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Необходимые расширения PHP', 'url' => ['/settings/php-requirements'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            [
                                'label' => 'Настройки',
                                'url' => ['/settings/main'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'badgeBgClass' => 'badge-info d-flex align-items-center',
                            ],
                            [
                                'label' => 'Отладка SOAP',
                                'url' => ['/log/debugging'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'badgeBgClass' => 'badge-info d-flex align-items-center',
                            ],
                            [
                                'label' => Yii::t('backend', 'reCAPTCHA'),
                                'icon' => "<img src=\"{$pathToSvg}\" width=\"18\" height=\"18\">",
                                'url' => ['/settings/recaptcha'],
                                'visible' => Yii::$app->user->can('administrator')
                            ],
                            ['label' => Yii::t('backend', 'Кэш'), 'url' => ['/cache/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            [
                                'label' => Yii::t('backend', 'Пересчёт хеш-суммы'),
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/settings/checksum'],
                                'visible' => Yii::$app->user->can('administrator')
                            ],
                            [
                                'label' => Yii::t('backend', 'Очистка данных'),
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/cleaner/index'],
                            ],
                            ['label' => Yii::t('backend', 'Настройка переменных окружения'), 'url' => ['/env-settings/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            [
                                'label' => Yii::t('backend', 'Настройка оформления'),
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/style-settings/index'],
                            ],
                            ['label' => Yii::t('backend', 'Настройка интеграций'), 'url' => ['/integrations/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                        ]
                    ]
                ];
                if (Yii::$app->hasModule('abiturient')) {
                    $items[] = [
                        'label' => 'Настройки личного кабинета поступающего',
                        'url' => '#',
                        'icon' => '<i class="align-self-center nav-icon fa fa-cogs ml-0"></i>',
                        'items' => [
                            ['label' => 'Справочники', 'url' => ['/dictionary/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Приемные кампании', 'url' => ['/admission/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Коды по умолчанию', 'url' => ['/settings/code'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Авторизация', 'url' => ['/settings/auth'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Тексты', 'url' => ['/settings/text'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Песочница', 'url' => ['/settings/sandbox'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Скан-копии', 'url' => ['/scan/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            [
                                'label' => 'Настройка системных скан-копий',
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/scan/system-scans-template'],
                                'visible' => Yii::$app->user->can('administrator'),
                            ],
                            ['label' => Yii::t('settings/questionary', 'Настройки анкеты'), 'url' => ['/settings/questionary'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'ПК модератора', 'url' => ['/manage/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            [
                                'label' => 'Настройки доступа',
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/settings/rolerule?isAbit=true'],
                                'visible' => Yii::$app->user->can('administrator')
                            ],
                            ['label' => 'Нормативные документы', 'url' => ['/regulation'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            [
                                'label' => 'Фильтры модератора',
                                'url' => ['/filters/index'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'
                            ],
                            [
                                'label' => 'Настройка хранилища',
                                'url' => ['/settings/storage'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'
                            ],
                            ['label' => 'Интерфейс модератора', 'url' => ['/settings/master-system-manager-interface'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            [
                                'label' => "Настройки блока данных<br>о родителях или<br>законных представителях",
                                'url' => ['/settings/parent-data'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'encode' => false
                            ],
                            ['label' => 'Настройки уведомлений', 'url' => ['/settings/notification'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Настройки чата', 'url' => ['/settings/chat'], 'icon' => '<i class="align-self-center nav-icon fa fa-comments-o"></i>'],
                            ['label' => Yii::t('backend', 'ПК для просмотра заявлений'), 'url' => ['/viewer/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            [
                                'label' => 'Управление подачей заявлений',
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/settings/application-settings'],
                                'visible' => Yii::$app->user->can('administrator'),
                            ],
                            [
                                'label' => 'Управление блокировкой заявлений',
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/bachelor-application/index'],
                                'visible' => Yii::$app->user->can('administrator'),
                            ],
                            [
                                'label' => 'Настройка главной страницы поступающего',
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/main-page-setting/index'],
                                'visible' => Yii::$app->user->can('administrator'),
                            ],
                            [
                                'label' => 'Настройки псевдонимов статусов проверки документа',
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/settings/document-check-status-aliases'],
                                'visible' => Yii::$app->user->can('administrator'),
                            ],
                            [
                                'label' => 'Настройки окна просмотра истории изменений',
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/settings/change-history-settings'],
                                'visible' => Yii::$app->user->can('administrator'),
                            ],
                        ]
                    ];
                }
                if (Yii::$app->hasModule('student')) {
                    $items[] = [
                        'label' => 'Настройки личного кабинета студента',
                        'url' => '#',
                        'icon' => '<i class="align-self-center nav-icon fa fa-cogs ml-0"></i>',
                        'items' => [
                            [
                                'label' => 'Настройки доступа',
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                                'url' => ['/settings/rolerule'],
                                'visible' => Yii::$app->user->can('administrator')
                            ],
                            [
                                'label' => 'Настройка главной страницы',
                                'url' => ['/settings/set-index-page'],
                                'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>',
                            ],
                            ['label' => 'Ссылки', 'url' => ['/settings/studentsidelinks'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                            ['label' => 'Форум', 'url' => ['/forum/index'], 'icon' => '<i class="align-self-center nav-icon fa fa-angle-double-right"></i>'],
                        ]
                    ];
                }

                echo Menu::widget([
                    'options' => [
                        'role' => 'menu',
                        'data-accordion' => 'false',
                        'data-widget' => 'treeview',
                        'class' => implode(' ', [
                            'nav nav-pills nav-sidebar flex-column',
                            Yii::$app->keyStorage->get('backend.nav-style', 'nav-flat'),
                            Yii::$app->keyStorage->get('backend.nav-compact') ? 'nav-compact' : null,
                            Yii::$app->keyStorage->get('backend.nav-child-indent') ? 'nav-child-indent' : null,
                        ]),
                    ],
                    'submenuTemplate' => '<ul class="nav nav-treeview">{items}</ul>',
                    'activateParents' => true,
                    'items' => $items
                ]) ?>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <?php if (Yii::$app->user->identity && Yii::$app->user->identity->isAdmin()) : ?>
            <?php [$state, $system_version, $portal_version] = EnvironmentManager::GetMigrationsApplyingStatus(); ?>

            <?php if (!$state) : ?>
                <div style="margin-top: 0;margin-bottom: 0;text-align: center;" class="alert alert-danger">
                    <strong>Внимание!</strong> Различаются версии базы данных (<?= $system_version ?>) и портала
                    (<?= $portal_version ?>). Необходимо применить миграции
                </div>
            <?php endif; ?>

            <?php $timeZoneLocal = date_default_timezone_get();
            $timeZoneGlobal = ini_get('date.timezone');
            if (strcmp($timeZoneLocal, $timeZoneGlobal) || strlen((string)$timeZoneGlobal) < 1) {
                echo Html::tag(
                    'div',
                    '<strong>В портале не установлен часовой пояс.</strong> Поступающие и модераторы не смогут работать с порталом до того, как проблема будет решена. Настройте временную зону.',
                    [
                        'class' => 'alert alert-danger',
                        'style' => 'margin-top: 0;margin-bottom: 0;text-align: center;'
                    ]
                );
            } ?>

            <?php if (!Yii::$app->releaseVersionProvider->isOneSServicesVersionMatches()) : ?>
                <div class="alert alert-danger" style="margin-top: 0;margin-bottom: 0;text-align: center;">
                    <?= Yii::t(
                        'header/admin-interface',
                        'Предупреждение о том, что версия Информационной системы вуза не удовлетворяет минимальным требованиям к версии сервисов: `версия Информационной системы вуза не удовлетворяет минимальным требованиям Портала к версии сервисов.`',
                    ) ?>
                </div>
            <?php endif; ?>

            <?php if (CodeSettingsManager::NeedToFillCodes()) : ?>
                <?php echo Html::tag(
                    'div',
                    Html::a(
                        'Есть незаполненные коды по умолчанию. Требуется заполнение для корректной работы портала.',
                        Url::toRoute(['/settings/code']),
                        ['class' => "alert-link"]
                    ),
                    [
                        'class' => 'alert alert-warning',
                        'style' => 'margin-top: 0;margin-bottom: 0;text-align: center;'
                    ]
                ); ?>
            <?php endif; ?>

            <?php if (!ChecksumManager::checkVendorChecksum()) : ?>
                <?php echo Html::tag(
                    'div',
                    Yii::t('backend', 'Внесены изменения в папку vendor. Корректная работа портала не гарантируется'),
                    [
                        'class' => 'alert alert-warning',
                        'style' => 'margin-top: 0;margin-bottom: 0;text-align: center;'
                    ]
                ); ?>
            <?php endif; ?>

            <?php if (!DictionaryUpdateHistory::hasUpdatedDictionariesAfterVersionMigrated()) : ?>
                <div class="alert alert-warning text-center" style="margin-bottom: 0;">
                    <?= Yii::t(
                        'header/admin-interface',
                        'Предупреждение для администратора о том, что после обновления портала не обновлены справочники: `После установки новой версии Портала вуза необходимо выполнить обновление справочников на странице "Настройки личного кабинета поступающего" - <a href="{url}" class="alert-link">"Справочники"</a>`',
                        [
                            'url' => Url::to(['/dictionary/index']),
                        ]
                    ) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="content-header">
            <div class="d-flex justify-content-end align-content-center">
                <?= Breadcrumbs::widget([
                    'tag' => 'ol',
                    'links' => $this->params['breadcrumbs'] ?? [],
                ]) ?>
            </div>

            <h2>
                <?= $this->title ?>

                <?php if (isset($this->params['subtitle'])) : ?>
                    <small><?= $this->params['subtitle'] ?></small>
                <?php endif; ?>
            </h2>
        </div>

        <div class="content">
            <?php if (
                Yii::$app->session->hasFlash('alert') &&
                ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body')
            ) : ?>
                <?= Alert::widget([
                    'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
                    'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
                ]) ?>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('alert-info')) : ?>
                <?= Alert::widget([
                    'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert-info'), 'body'),
                    'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert-info'), 'options'),
                ]) ?>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>
</div>

<?php $this->endContent();
