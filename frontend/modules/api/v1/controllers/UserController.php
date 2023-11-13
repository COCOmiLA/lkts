<?php

namespace frontend\modules\api\v1\controllers;

use frontend\modules\api\v1\resources\User;
use yii\web\HttpException;




class UserController extends BaseController
{
    


    public $modelClass = 'frontend\modules\api\v1\resources\User';

    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ]
        ];
    }
    
    public function findModel($id)
    {
        $model = User::findActive()
            ->andWhere(['id' => (int) $id])
            ->one();
        if (!$model) {
            throw new HttpException(404);
        }
        return $model;
    }
    
    public function actionCurrent($accessToken)
    {
        $user = User::findIdentityByAccessToken($accessToken);
        return $user;
    }
}
