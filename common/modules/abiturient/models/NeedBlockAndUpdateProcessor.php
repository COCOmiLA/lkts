<?php


namespace common\modules\abiturient\models;


use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\Speciality;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use stdClass;
use Yii;

class NeedBlockAndUpdateProcessor
{
    const EMPTY_DATE = '0001-01-01T00:00:00';

    private static function getNeedBlockAndUpdate(array $data): array
    {
        if ($data) {
            $result = Yii::$app->soapClientAbit->load_with_caching(
                'NeedBlockAndUpdate',
                $data
            );
            if (isset($result->return, $result->return->Update, $result->return->Block)) {
                $block = (int)$result->return->Block == 1;
                $update = (int)$result->return->Update == 1;
                return [$update, $block];
            }
        }
        return [false, true];
    }

    public static function getProcessedNeedBlockAndUpdate(BachelorApplication $app): array
    {
        if (isset($app->user, $app->user->userRef)) {
            $date = $app->getCheckDate();
            $consentDate = $app->getCheckConsentDate();
            $built_entrant = $app->buildEntrantArray();
            if (isset($date, $app->type, $app->type->campaign) && !empty($consentDate)) {
                [$update, $block] = NeedBlockAndUpdateProcessor::getNeedBlockAndUpdate([
                    'AbiturientCode' => $app->user->userRef->reference_id,
                    'Date' => $date,
                    'IdPK' => $app->type->campaign->referenceType->reference_id,
                    'Entrant' => $built_entrant,
                    'ConsentDate' => $consentDate,
                ]);
                if (!$update) {
                    
                    $update = UserReferenceTypeManager::IsUserRefDataVersionOutdated($app->user);
                }
                return [$update, $block];
            }
        }
        return [false, false];
    }

    public static function GetMessageAboutRequiredDeclineForUpdate(BachelorApplication $application): string
    {
        $base = Yii::$app->configurationManager->getText('need_update_app_from_one_s', $application->type);

        $date = $application->getCheckDate();
        $consentDate = $application->getCheckConsentDate();
        $is_versions_mismatch = UserReferenceTypeManager::IsUserRefDataVersionOutdated($application->user);
        if ($is_versions_mismatch) {
            $base = Yii::$app->configurationManager->getText('need_update_questionary_from_one_s', $application->type);
        }

        if ($date) {
            $base = Yii::t(
                'sandbox/moderate/all',
                'Тело сообщения с датой актуальности заявления для устаревшего заявления; на стр. проверки анкеты поступающего: `{messageAboutRequiredDeclineForUpdate}<br />Дата заявления в Личном кабинете поступающего: {date}`',
                [
                    'messageAboutRequiredDeclineForUpdate' => $base,
                    'date' => $date,
                ]
            );
        }
        if ($consentDate && $consentDate != NeedBlockAndUpdateProcessor::EMPTY_DATE) {
            $base = Yii::t(
                'sandbox/moderate/all',
                'Тело сообщения с датой актуальности согласия для устаревшего заявления; на стр. проверки анкеты поступающего: `{messageAboutRequiredDeclineForUpdate}<br />Дата подачи согласия в Личном кабинете поступающего: {consentDate}`',
                [
                    'messageAboutRequiredDeclineForUpdate' => $base,
                    'consentDate' => $consentDate,
                ]
            );
        }
        return $base;
    }

    public static function GetMessageAboutBlockedBy1C(BachelorApplication $application): string
    {
        return Yii::t(
            'sandbox/moderate/all',
            'Текст сообщения о невозможности принять заявление так как в Информационной системе вуза есть не проведённые сущности: `Заявление заблокировано. Для разблокировки необходимо провести или пометить на удаление документы заявлений и согласий на зачисление поступающего.`'
        );
    }
}
