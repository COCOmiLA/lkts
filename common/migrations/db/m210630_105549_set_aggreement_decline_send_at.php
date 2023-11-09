<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\EmptyCheck;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\NeedBlockAndUpdateProcessor;
use yii\helpers\ArrayHelper;




class m210630_105549_set_aggreement_decline_send_at extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $app_ids_with_broken_declines = [];

        $declines_to_restore = AgreementDecline::find()
            ->andWhere(['archive' => false])
            ->andWhere(['sended_at' => [null, 0]])
            ->with(['agreement.speciality.application'])
            ->all();
        foreach ($declines_to_restore ?? [] as $decline) {
            $tmp = ArrayHelper::getValue($decline, 'agreement.speciality.application.id');
            if (!is_null($tmp)) {
                $app_ids_with_broken_declines[] = $tmp;
            }
        }

        $applications = BachelorApplication::find()
            ->with(['user'])
            ->andWhere(['id' => array_unique($app_ids_with_broken_declines)])
            ->all();

        foreach ($applications ?? [] as $app) {
            $user_code = ArrayHelper::getValue($app, 'user.userRef.reference_id');
            $_code = ArrayHelper::getValue($app, 'type.campaign.code');

            if (isset($_code, $user_code)) {
                $request_data = [
                    'AbiturientCode' => $user_code,
                    'IdPK' => $_code,
                    'Entrant' => $app->buildEntrantArray()
                ];
                $zayavleniya = false;
                try {
                    $zayavleniya = Yii::$app->soapClientAbit->load(
                        'GetZayavleniya',
                        $request_data
                    );
                } catch (Throwable $e) {
                    Yii::error("Ошибка при выполнении метода GetZayavleniya: {$e->getMessage()}");
                }

                if ($zayavleniya === false) {
                    continue;
                }

                if (!isset($zayavleniya->return->UniversalResponse->Complete) || $zayavleniya->return->UniversalResponse->Complete == "0") {
                    $log = [
                        'data' => $request_data,
                        'result' => $zayavleniya,
                    ];
                    Yii::error('Ошибка при выполнении метода GetZayavleniya: ' . $zayavleniya->return->UniversalResponse->Description . ' ' . PHP_EOL . print_r($log, true));
                    continue;
                }


                if (isset($zayavleniya->return->ApplicationString)) {
                    if (!is_array($zayavleniya->return->ApplicationString)) {
                        $zayavleniya->return->ApplicationString = [$zayavleniya->return->ApplicationString];
                    }
                    foreach ($zayavleniya->return->ApplicationString as $appString) {
                        $_app_code = $appString->idApplicationString;
                        if ($appString->Consent || EmptyCheck::isEmpty($_app_code)) {
                            continue;
                        }
                        $decline = AgreementDecline::find()
                            ->leftJoin('admission_agreement', 'agreement_decline.agreement_id = admission_agreement.id')
                            ->leftJoin('bachelor_speciality', 'admission_agreement.speciality_id = bachelor_speciality.id')
                            ->leftJoin('bachelor_application', 'bachelor_speciality.application_id = bachelor_application.id')
                            ->where(['agreement_decline.sended_at' => null])
                            ->andWhere(['bachelor_speciality.application_id' => $app->id])
                            ->andWhere(['agreement_decline.archive' => false])
                            ->andWhere(['bachelor_speciality.application_code' => $_app_code])
                            ->orderBy(['agreement_decline.updated_at' => SORT_DESC])
                            ->one();
                        if (!is_null($decline)) {
                            if ($appString->ConsentDate != NeedBlockAndUpdateProcessor::EMPTY_DATE) {
                                $decline->sended_at = strtotime($appString->ConsentDate);
                                if ($decline->save(true, ['sended_at'])) {
                                    $decline->touch('updated_at'); 
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
