<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\actions;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use Yii;
use yii\base\Action;
use yii\web\Response;







class MemberAction extends Action
{
    


    public $view;

    





    public function run($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->controller->redirect(['members/index']);
        }

        $user = User::find()->where(['and',
            ['id' => $id],
            ['or',
                ['slug' => $slug],
                ['slug' => ''],
                ['slug' => null],
            ]
        ])->limit(1)->one();
        if (empty($user)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->controller->redirect(['members/index']);
        }
        return $this->controller->render($this->view, ['user' => $user]);
    }
}
