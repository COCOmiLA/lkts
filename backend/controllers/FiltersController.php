<?php

namespace backend\controllers;

use backend\models\FiltersSetting;
use common\models\User;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class FiltersController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMINISTRATOR]
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $showFilter = [];
        $showColumn = [];
        $sortablePageElements = '';
        $filters = FiltersSetting::find()
            ->orderBy('serial')
            ->all();
        if (!empty($filters)) {
            $showFilter = ArrayHelper::map($filters, 'name', 'show_filter');
            $showColumn = ArrayHelper::map($filters, 'name', 'show_column');
        }
        $model = DynamicModel::validateData(compact(
            'filters',
            'showColumn',
            'showFilter',
            'sortablePageElements'
        ));

        if (Yii::$app->request->isPost) {
            if (FiltersSetting::loadFromPost($model)) {
                Yii::$app->session->setFlash('alert', [
                    'body' => 'Изменения успешно сохранены',
                    'options' => ['class' => 'alert-success']
                ]);

                $filters = FiltersSetting::find()
                    ->orderBy('serial')
                    ->all();
                if (!empty($filters)) {
                    $showFilter = ArrayHelper::map($filters, 'name', 'show_filter');
                    $showColumn = ArrayHelper::map($filters, 'name', 'show_column');
                }
                $model = DynamicModel::validateData(compact(
                    'filters',
                    'showColumn',
                    'showFilter',
                    'sortablePageElements'
                ));
            } else {
                Yii::$app->session->setFlash('alert', [
                    'body' => 'Произошла ошибка сохранения',
                    'options' => ['class' => 'alert-danger']
                ]);
            }
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
