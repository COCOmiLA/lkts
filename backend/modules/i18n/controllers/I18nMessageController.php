<?php

namespace backend\modules\i18n\controllers;

use backend\modules\i18n\models\I18nMessage;
use backend\modules\i18n\models\I18nSourceMessage;
use backend\modules\i18n\models\search\I18nMessageSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;




class I18nMessageController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    



    public function actionIndex()
    {
        $searchModel = new I18nMessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        Url::remember(Yii::$app->request->getUrl(), 'i18n-messages-filter');

        $languages = ArrayHelper::map(
            I18nMessage::find()->select('language')->distinct()->all(),
            'language',
            'language'
        );
        $categories = ArrayHelper::map(
            I18nSourceMessage::find()->select('category')->distinct()->all(),
            'category',
            'category'
        );

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'languages' => $languages,
            'categories' => $categories
        ]);
    }

    




    public function actionCreate()
    {
        $model = new I18nMessage();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    






    public function actionUpdate($id, $language)
    {
        $model = $this->findModel($id, $language);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Url::previous('i18n-messages-filter') ?: ['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    






    public function actionDelete($id, $language)
    {
        $this->findModel($id, $language)->delete();

        return $this->redirect(['index']);
    }

    







    protected function findModel($id, $language)
    {
        if (($model = I18nMessage::findOne(['id' => $id, 'language' => $language])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
