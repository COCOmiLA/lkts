<?php








use common\models\User as MainAppUser;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\components\forumIn\forum\bizley\podium\src\rbac\Rbac;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;

$items = [['label' => Yii::t('podium/view', 'Home'), 'url' => ['forum/index']]];

$podiumModule = Podium::getInstance();

if (Podium::getInstance()->user->isGuest) {
    if (Podium::getInstance()->podiumConfig->get('members_visible')) {
        $items[] = [
            'label' => Yii::t('podium/view', 'Members'),
            'url' => ['members/index'],
            'active' => $this->context->id == 'members'
        ];
    }
    if ($podiumModule->userComponent === true && $this->context->accessType === 1) {
        if ($podiumModule->podiumConfig->get('registration_off') != '1') {
            $items[] = ['label' => Yii::t('podium/view', 'Register'), 'url' => $podiumModule->registerUrl];
        }
        $items[] = ['label' => Yii::t('podium/view', 'Sign in'), 'url' => $podiumModule->loginUrl];
    }
} else {
    $podiumUser = common\modules\student\components\forumIn\forum\bizley\podium\src\models\User::findMe();
    $messageCount = $podiumUser->newMessagesCount;
    $subscriptionCount = $podiumUser->subscriptionsCount;

    if (User::can(Rbac::ROLE_ADMIN)) {
        $items[] = [
            'label' => Yii::t('podium/view', 'Administration'),
            'url' => ['admin/index'],
            'active' => $this->context->id == 'admin'
        ];
    }
    $items[] = [
        'label' => Yii::t('podium/view', 'Members'),
        'url' => ['members/index'],
        'active' => $this->context->id == 'members'
    ];

    if ($podiumUser->inherited_id != 0)
        $username = (new MainAppUser)->find()->where(['id' => $podiumUser->inherited_id])->one()->username;
    else
        $username = $podiumUser->podiumName;
    $items[] = [
        'label' => Yii::t('podium/view', 'Profile ({name})', ['name' => $username != null ? $username : $podiumUser->podiumName])
                    . ($subscriptionCount ? ' ' . Html::tag('span', $subscriptionCount, ['class' => 'badge']) : ''),
        'url' => ['profile/index'],
        'items' => [
            ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']],
            ['label' => Yii::t('podium/view', 'Account Details'), 'url' => ['profile/details']],
            ['label' => Yii::t('podium/view', 'Forum Details'), 'url' => ['profile/forum']],
            ['label' => Yii::t('podium/view', 'Subscriptions'), 'url' => ['profile/subscriptions']],
        ]
    ];
    $items[] = [
        'label' => Yii::t('podium/view', 'Messages') . ($messageCount ? ' ' . Html::tag('span', $messageCount, ['class' => 'badge']) : ''),
        'url' => ['messages/inbox'],
        'items' => [
            ['label' => Yii::t('podium/view', 'Inbox'), 'url' => ['messages/inbox']],
            ['label' => Yii::t('podium/view', 'Sent'), 'url' => ['messages/sent']],
            ['label' => Yii::t('podium/view', 'New Message'), 'url' => ['messages/new']],
        ]
    ];
    if ($podiumModule->userComponent === true) {
        $items[] = ['label' => Yii::t('podium/view', 'Sign out'), 'url' => ['profile/logout'], 'linkOptions' => ['data-method' => 'post']];
    }
}

NavBar::begin([
    'brandLabel' => $podiumModule->podiumConfig->get('name'),
    'brandUrl' => ['forum/index'],
    'options' => ['class' => 'navbar-inverse navbar-default', 'id' => 'top'],
    'innerContainerOptions' => ['class' => 'container-fluid',]
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'encodeLabels' => false,
    'activateParents' => true,
    'items' => $items,
]);
NavBar::end();
