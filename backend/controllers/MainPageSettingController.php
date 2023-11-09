<?php

namespace backend\controllers;

use backend\models\MainPageInstructionHeader;
use backend\models\MainPageInstructionText;
use backend\models\MainPageInstructionVideo;
use backend\models\MainPageSetting;
use common\models\errors\RecordNotValid;
use common\models\User;
use Throwable;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class MainPageSettingController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['post']]
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => [User::ROLE_ADMINISTRATOR]
                ]],
            ],
        ];
    }

    public function actionIndex()
    {
        $settingModel = new MainPageSetting();
        if (Yii::$app->request->isPost) {
            $user = Yii::$app->user->identity;

            $transaction = Yii::$app->db->beginTransaction();
            $load = MainPageSetting::loadFromPost($user);

            try {
                MainPageSetting::updateAll(['number' => null]);

                $tnMainPageSetting = MainPageSetting::tableName();
                $idSettingsNotToDelete = [];

                foreach ($load as $i => $instructionPoint) {
                    

                    $idSettingsNotToDelete[] = $this->saveOneInstructions($instructionPoint, $i + 1);
                }

                MainPageSetting::deleteAll(['NOT IN', "{$tnMainPageSetting}.id", $idSettingsNotToDelete]);

                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        $instructions = MainPageSetting::getInstructions();
        return $this->render(
            'index',
            compact([
                'instructions',
                'settingModel',
            ])
        );
    }

    





    private function saveOneInstructions(ActiveRecord $instructionPoint, int $number): int
    {
        $tnMainPageSetting = MainPageSetting::tableName();

        $loadSetting = MainPageSetting::find()
            ->andWhere(["{$tnMainPageSetting}.id" => $instructionPoint->main_page_setting_id])
            ->one();
        if (!$loadSetting) {
            $loadSetting = new MainPageSetting();
        }

        $loadSetting->number = $number;
        if (!$loadSetting->save()) {
            throw new RecordNotValid($loadSetting);
        }

        $instructionPoint->main_page_setting_id = $loadSetting->id;
        if (!$instructionPoint->save(true, array_keys($instructionPoint->attributes))) {
            throw new RecordNotValid($instructionPoint);
        }

        return $loadSetting->id;
    }
}
