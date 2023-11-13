<?php


namespace backend\controllers;


use common\modules\abiturient\models\bachelor\ApplicationSearch;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;

class BachelorApplicationController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new ApplicationSearch(['statusBlock'=> BachelorApplication::BLOCK_STATUS_ENABLED]);
        $applicationsDataProvider = $searchModel->search(Yii::$app->request->queryParams, 'all', null);
        return $this->render('index', [
            'dataProvider' => $applicationsDataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUnblock($id = null)
    {
        if ($id) {
            $application = BachelorApplication::findOne(['id' => $id]);
            if ($application) {
                $application->fullyUnblockApplication();
            }
        } else {
            BachelorApplication::updateAll(['block_status' => 0, 'blocker_id' => null]);
        }

        return $this->redirect(Url::toRoute('index', 302));
    }

}