<?php
namespace frontend\modules\api\v1\controllers;

use frontend\modules\api\v1\resources\Article;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;





class ArticleController extends BaseController
{
    


    public $modelClass = 'frontend\modules\api\v1\resources\Article';
    


    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items'
    ];

    


    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
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

    


    public function prepareDataProvider()
    {
        return new ActiveDataProvider(array(
            'query' => Article::find()->published()
        ));
    }

    




    public function findModel($id)
    {
        $model = Article::find()
            ->published()
            ->andWhere(['id' => (int) $id])
            ->one();
        if (!$model) {
            throw new HttpException(404);
        }
        return $model;
    }
}
