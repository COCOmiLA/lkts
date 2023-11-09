<?php

use backend\models\DictionaryUpdateHistory;
use backend\models\FaviconSettings;
use common\assets\AjaxBtnManagerAsset;
use common\assets\SubmitBtnManagerAsset;
use common\components\notification\NotificationSettingsManager;
use common\components\notification\widgets\PopupNotificationWidget;
use common\models\settings\LogoSetting;
use common\models\User;
use frontend\assets\FrontendAsset;
use frontend\assets\LogoAssets;
use frontend\assets\NavBarAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;


if ((new FaviconSettings())->hasAppearanceFile()) {

    $this->registerLinkTag([
        'rel' => 'icon',
        'type' => 'image/ico',
        'href' => '/custom-favicon.ico'
    ]);
}

FrontendAsset::register($this);
NavBarAssets::register($this);
LogoAssets::register($this);
AjaxBtnManagerAsset::register($this);
SubmitBtnManagerAsset::register($this);

$this->registerJsVar(
    'alertErrorMessageForAjaxBtnManager',
    Yii::t(
        'abiturient/errors',
        'Сообщение поступающему о ошибке сохранения формы скан-копий: `Возникла ошибка сохранения формы. Обратитесь к администратору.`'
    )
);

$isGuest = Yii::$app->user->isGuest;
$isAbit = Yii::$app->user->can(User::ROLE_ABITURIENT);
$languages = Yii::$app->localizationManager->getAvailableLocales(true);

$pullDirection = $isGuest ? 'float-right' : '';
$containerClass = Yii::$app->user->can('manager') ? '' : 'container';

$style = '<style>';
$logoSettings = [];
$logoWithUsername = null;
$logoWithoutUsername = null;
$tableLogoSettingExist = Yii::$app->db->schema->getTableSchema(LogoSetting::tableName()) !== null;
if ($tableLogoSettingExist) {
    $logoSettings = LogoSetting::find()
        ->andWhere(['>', 'width', 0])
        ->andWhere(['>', 'height', 0])
        ->all();
}
foreach ($logoSettings as $logoSetting) {
    if ($logoSetting->name == 'logo-without-username') {
        $logoWithoutUsername = $logoSetting;
    } elseif ($logoSetting->name == 'logo-with-username') {
        $logoWithUsername = $logoSetting;
    }

    $style .= "
        .{$logoSetting->name} {
            padding: 0;
            width: {$logoSetting->width}px;
            height: {$logoSetting->height}px;
        }
    ";
}
$style .= '</style>';

?>

<?php $this->beginPage(); ?>

    <!DOCTYPE html>

    <html lang="<?= Yii::$app->language ?>">

    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>
            <?= Html::encode($this->title); ?>
        </title>

        <?php $this->head(); ?>
        <?= Html::csrfMetaTags(); ?>

        <?= $style ?>
    </head>

    <body>
    <?php $session = Yii::$app->session; ?>
    <?php if ($session->has('transfer')) : ?>
        <?php $username = ArrayHelper::getValue(Yii::$app, 'user.identity.username', ''); ?>

        <div class="alert alert-warning alert-for-transfer">
            <?= Yii::t(
                'header/admin-interface',
                'Алерт предупреждающий о том, что пользователь изменён: `<strong>Внимание!</strong> Вы работаете от имени пользователя {username}. <a href="{url}" class="alert-link">Завершить сеанс.</a>`',
                [
                    'url' => Url::to('/transfer/came-back'),
                    'username' => $username
                ]
            ); ?>
        </div>
    <?php endif; ?>

    <?php if (!DictionaryUpdateHistory::hasUpdatedDictionariesAfterVersionMigrated()) : ?>
        <div class="alert alert-warning text-center">
            <?= Yii::t(
                'header/admin-interface',
                'Предупреждение о том, что после обновления портала не обновлены справочники: `После установки новой версии Портала вуза не обновлена справочная информация. Обратитесь к администратору.`',
            ); ?>
        </div>
    <?php endif; ?>
    <nav class="navbar navbar-light portal-navbar <?= $containerClass ?> nav-bar-background-color navbar-expand-md"
         style="">
        <?php if ($isAbit && NotificationSettingsManager::isWidgetEnabled()) : ?>
            <?php echo PopupNotificationWidget::widget(); ?>
        <?php endif; ?>

        <?php if ($isGuest) : ?>
            <?php if ($logoWithoutUsername && $logoWithoutUsername->hasAppearanceFile()) : ?>
                <?= Html::a(
                    Html::img(
                        Url::to($logoWithoutUsername->getLogoFileUrl()),
                        [
                            'class' => 'logo-without-username',
                            'alt' => Yii::t('header/logo', 'Подпись логотип портала, для неавторизованных пользователей на странице авторизации: `Логотип портала, для неавторизованных пользователей`')
                        ]
                    ),
                    '#',
                    ['class' => 'navbar-brand li-like-padding nav-logo']
                ); ?>
            <?php endif; ?>
        <?php else : ?>
            <?php if ($logoWithUsername && $logoWithUsername->hasAppearanceFile()) : ?>
                <?= Html::a(
                    Html::img(
                        Url::to($logoWithUsername->getLogoFileUrl()),
                        [
                            'class' => 'logo-with-username',
                            'alt' => Yii::t('header/logo', 'Подпись логотип портала, для неавторизованных пользователей на странице авторизации: `Логотип портала, для неавторизованных пользователей`')
                        ]
                    ),
                    '#',
                    ['class' => 'navbar-brand li-like-padding nav-logo']
                ); ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$isGuest && isset(Yii::$app->user->identity)) : ?>
            <?php $isStudent = Yii::$app->user->identity->isInRole(User::ROLE_STUDENT);
            $isTeacher = Yii::$app->user->identity->isInRole(User::ROLE_TEACHER); ?>
            <?php if ($isStudent || $isTeacher) : ?>
                <?php if ($isStudent) {
                    $url = Url::toRoute(['/student/index']);
                } elseif ($isTeacher) {
                    $url = Url::toRoute(['/teacher/index']);
                } ?>
                <?= Html::a(
                    Yii::t(
                        'header/btns',
                        'Подпись кнопки перехода на главную страницу для студента: `На главную`'
                    ),
                    $url,
                    ['class' => 'navbar-brand li-like-padding']
                ); ?>
            <?php elseif ($isAbit) : ?>
                <?= Html::a(
                    Yii::$app->user->identity->getPublicIdentity(),
                    Url::toRoute(['/']),
                    ['class' => 'navbar-brand li-like-padding']
                ); ?>
            <?php endif; ?>
        <?php endif; ?>

        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#user-tabs"
                aria-controls="user-tabs" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse collapse-border" id="user-tabs">
            <ul class="nav navbar-nav" style="margin-right: auto; margin-left: auto;">
                <?php if (!$isGuest) : ?>
                    <?php if (!$isAbit) : ?>
                        <li class="nav-item">
                            <?= Html::a(
                                Yii::$app->user->identity->getPublicIdentity(),
                                Url::toRoute(['/']),
                                ['class' => 'nav-link']
                            ); ?>
                        </li>

                        <li class="nav-item dropdown-divider"></li>
                    <?php endif; ?>

                    <?php if (!empty($languages)) : ?>
                        <li class="nav-item dropdown">
                            <?= Html::a(
                                Yii::t('header/language-btn', 'Надпись на кнопке переключающей язык интерфейса: `Язык`'),
                                '#',
                                [
                                    'role' => 'button',
                                    'aria-haspopup' => 'true',
                                    'aria-expanded' => 'false',
                                    'data-toggle' => 'dropdown',
                                    'class' => 'dropdown-toggle nav-bar-background-color nav-link',
                                ]
                            ); ?>

                            <ul class="dropdown-menu nav-bar-background-color">
                                <?php foreach ($languages as $language => $alias) : ?>
                                    <li class="nav-bar-background-color">
                                        <?= Html::a(
                                            $alias,
                                            Url::toRoute(['/site/set-locale', 'language' => $language])
                                        ); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>

                        <li class="nav-item  dropdown-divider"></li>
                    <?php endif; ?>

                    <?php if (
                        (Yii::$app->user->can(User::ROLE_STUDENT) ||
                            Yii::$app->user->can(User::ROLE_TEACHER)
                        ) &&
                        !Yii::$app->user->can(User::ROLE_MANAGER)
                    ) : ?>
                        <li class="nav-item dropdown">
                            <?= Html::a(
                                Yii::t('header/btns', 'Подпись кнопки перехода на страницу с настройками профиля: `Настройки`'),
                                '#',
                                [
                                    'role' => 'button',
                                    'aria-haspopup' => 'true',
                                    'aria-expanded' => 'false',
                                    'data-toggle' => 'dropdown',
                                    'class' => 'dropdown-toggle nav-bar-background-color nav-link',
                                ]
                            ); ?>

                            <ul class="dropdown-menu nav-bar-background-color">
                                <li class="nav-bar-background-color">
                                    <?= Html::a(
                                        Yii::t(
                                            'header/btns',
                                            'Подпись кнопки перехода на страницу смены пароля: `Сменить пароль`'
                                        ),
                                        Url::toRoute(['/user/sign-in/change-password'])
                                    ); ?>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown-divider"></li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <?= Html::a(
                            Yii::t(
                                'header/btns',
                                'Подпись кнопки выхода из аккаунта: `Выход`'
                            ),
                            Url::toRoute(['/user/sign-in/logout']),
                            ['class' => 'nav-link']
                        ); ?>
                    </li>
                <?php elseif ($isGuest) : ?>
                    <!--                    Логотип омгупса, на всех страницах-->
                    <?php if (Yii::$app->controller->action->id === 1): ?>

                    <?php else: ?>

                        <video width="280" autoplay muted>
                            <source src="/SredAnim.mp4" >
                        </video>

                        <li class="nav-item">
                            <?= Html::a(
                                Yii::t(
                                    'header/btns',
                                    'logo'
                                ),
                                Url::toRoute(['/user/sign-in/login']),
                                ['class' => 'navbar-brand li-like-padding nav-link']
                            ); ?>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <?php $this->beginBody(); ?>

    <?= $content ?>

    <?php $this->endBody(); ?>
    </body>

    </html>
<?php $this->endPage();