<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\controllers;

use common\modules\student\components\forumIn\forum\bizley\podium\src\filters\AccessControl;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\UserSearch;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;
use yii\web\Response;








class MembersController extends BaseController
{
    


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'denyCallback' => function ($rule, $action) {
                    return $this->redirect(['account/login']);
                },
                'rules' => [
                    ['class' => 'common\modules\student\components\forumIn\forum\bizley\podium\src\filters\InstallRule'],
                    [
                        'allow' => true,
                        'roles' => $this->module->podiumConfig->get('members_visible') ? ['@', '?'] : ['@'],
                    ],
                ],
            ],
        ];
    }

    




    public function actions()
    {
        return [
            'posts' => [
                'class' => 'common\modules\student\components\forumIn\forum\bizley\podium\src\actions\MemberAction',
                'view' => 'posts',
            ],
            'threads' => [
                'class' => 'common\modules\student\components\forumIn\forum\bizley\podium\src\actions\MemberAction',
                'view' => 'threads',
            ],
        ];
    }

    




    public function actionFieldlist($q = null)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['forum/index']);
        }
        return User::getMembersList($q);
    }

    




    public function actionIgnore($id = null)
    {
        if (Podium::getInstance()->user->isGuest) {
            return $this->redirect(['forum/index']);
        }

        $model = User::find()->where([
                'and',
                ['id' => (int)$id],
                ['!=', 'status', User::STATUS_REGISTERED]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }

        $logged = User::loggedId();

        if ($model->id == $logged) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not ignore your own account.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }

        if ($model->id == User::ROLE_ADMIN) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not ignore Administrator.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }

        if ($model->updateIgnore($logged)) {
            if ($model->isIgnoredBy($logged)) {
                $this->success(Yii::t('podium/flash', 'User is now ignored.'));
            } else {
                $this->success(Yii::t('podium/flash', 'User is not ignored anymore.'));
            }
        } else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while performing this action.'));
        }
        return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
    }

    



    public function actionIndex()
    {
        $searchModel = new UserSearch();
        return $this->render('index', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get(), true),
            'searchModel' => $searchModel
        ]);
    }

    



    public function actionMods()
    {
        $searchModel = new UserSearch();
        return $this->render('mods', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get(), true, true),
            'searchModel' => $searchModel
        ]);
    }

    





    public function actionView($id = null, $slug = null)
    {
        $model = User::find()->where(['and',
                ['id' => $id],
                ['!=', 'status', User::STATUS_REGISTERED],
                ['or',
                    ['slug' => $slug],
                    ['slug' => ''],
                    ['slug' => null],
                ]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }
        return $this->render('view', ['model' => $model]);
    }

    





    public function actionFriend($id = null)
    {
        if (Podium::getInstance()->user->isGuest) {
            return $this->redirect(['forum/index']);
        }

        $model = User::find()->where(['and',
                ['id' => $id],
                ['!=', 'status', User::STATUS_REGISTERED]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }

        $logged = User::loggedId();

        if ($model->id == $logged) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not befriend your own account.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }

        if ($model->updateFriend($logged)) {
            if ($model->isBefriendedBy($logged)) {
                $this->success(Yii::t('podium/flash', 'User is your friend now.'));
            } else {
                $this->success(Yii::t('podium/flash', 'User is not your friend anymore.'));
            }
        } else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while performing this action.'));
        }
        return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
    }
}
