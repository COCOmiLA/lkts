<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\actions;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use Yii;
use yii\base\Action;
use yii\web\Response;







class AdminAction extends Action
{
    


    public $fromRole;

    


    public $toRole;

    


    public $method;

    


    public $restrictMessage;

    


    public $successMessage;

    


    public $errorMessage;

    




    public function run($id = null)
    {
        $model = User::find()->where(['id' => $id])->limit(1)->one();
        if (empty($model)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find User with this ID.'));
            return $this->controller->redirect(['admin/members']);
        }
        if ($model->role != $this->fromRole) {
            $this->controller->error($this->restrictMessage);
            return $this->controller->redirect(['admin/members']);
        }
        if (call_user_func([$model, $this->method], $this->toRole)) {
            $this->controller->success($this->successMessage);
            if ($this->method == 'promoteTo') {
                return $this->controller->redirect(['admin/mods', 'id' => $model->id]);
            }
            return $this->controller->redirect(['admin/members']);
        }
        $this->controller->error($this->errorMessage);
        return $this->controller->redirect(['admin/members']);
    }
}
