<?php

namespace common\models\repositories;

use common\components\RegulationRelationManager;
use common\models\Attachment;
use common\models\errors\RecordNotValid;
use common\models\Regulation;
use common\models\UserRegulation;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\db\ActiveQuery;

class UserRegulationRepository
{
    




    public static function GetUserRegulationsByQuestionaryAndRelatedEntity(AbiturientQuestionary $questionary, $relatedEntity): array
    {
        
        $user_regulations = self::GetUserRegulationsByQuestionaryAndRelatedEntityQuery($questionary, $relatedEntity)->all();
        
        foreach ($user_regulations as $user_regulation) {
            if (!$user_regulation->abiturient_questionary_id && in_array($user_regulation->regulation->related_entity, [
                RegulationRelationManager::RELATED_ENTITY_REGISTRATION,
                RegulationRelationManager::RELATED_ENTITY_QUESTIONARY
            ])) {
                $user_regulation->abiturient_questionary_id = $questionary->id;
                if (!$user_regulation->save(true, ['abiturient_questionary_id'])) {
                    throw new RecordNotValid($user_regulation);
                }
            }
        }
        return $user_regulations;
    }

    




    public static function GetUserRegulationsByQuestionaryAndRelatedEntityQuery(AbiturientQuestionary $questionary, $relatedEntity): ActiveQuery
    {
        return UserRegulation::find()
            ->joinWith('regulation')
            ->andWhere([
                'owner_id' => $questionary->user_id,
                'abiturient_questionary_id' => [null, $questionary->id],
                'regulation.related_entity' => $relatedEntity
            ]);
    }

    




    public static function GetUserRegulationsByApplicationAndRelatedEntity(BachelorApplication $application, $relatedEntity = null): ActiveQuery
    {
        if (empty($relatedEntity)) {
            $relatedEntity = RegulationRelationManager::GetFullRelatedListForApplication();
        }

        return self::GetRawUserRegulationsByApplicationAndRelatedEntity($application, $relatedEntity);
    }

    




    public static function GetUserRegulationsWithFilesByApplicationAndRelatedEntity(BachelorApplication $application, $relatedEntity = null): array
    {
        if (empty($relatedEntity)) {
            $relatedEntity = RegulationRelationManager::GetFullRelatedListForApplication();
        }

        return self::GetRawUserRegulationsByApplicationAndRelatedEntity($application, $relatedEntity)
            ->joinWith('regulation')
            ->andWhere(['not', ['regulation.attachment_type' => null]])->all();
    }

    




    public static function GetUserRegulationsWithFilesByQuestionaryAndRelatedEntity(AbiturientQuestionary $questionary, $relatedEntity = null): array
    {
        return self::GetUserRegulationsByQuestionaryAndRelatedEntityQuery($questionary, $relatedEntity)
            ->joinWith('regulation')
            ->andWhere(['not', ['regulation.attachment_type' => null]])->all();
    }

    




    public static function GetRawUserRegulationsByApplicationAndRelatedEntity(BachelorApplication $application, $relatedEntity): ActiveQuery
    {
        return UserRegulation::find()
            ->joinWith('regulation')
            ->andWhere([
                'owner_id' => $application->user_id,
                'application_id' => $application->id,
                'regulation.related_entity' => $relatedEntity
            ]);
    }

    





    public static function GetUnfilledRequiredRegulationsQuery(AbiturientQuestionary $questionary, ?BachelorApplication $application): ActiveQuery
    {
        $params = [
            ':user_id' => $questionary->user_id,
            ':questionary_id' => $questionary->id,
        ];
        $on = "user_regulation.regulation_id = regulation.id and user_regulation.owner_id = :user_id";
        if ($application) {
            $on .= " and (user_regulation.abiturient_questionary_id = :questionary_id or user_regulation.application_id = :application_id)";
            $params[':application_id'] = $application->id;
        } else {
            $on .= " and user_regulation.abiturient_questionary_id = :questionary_id";
        }

        return Regulation::find()
            ->leftJoin('user_regulation', $on, $params)
            ->where([
                'regulation.confirm_required' => '1',
                'user_regulation.id' => null
            ]);
    }

    





    public static function CheckRequiredRegulations(AbiturientQuestionary $questionary, $relatedEntity, ?BachelorApplication $application): bool
    {
        if (!is_array($relatedEntity)) {
            $relatedEntity = [$relatedEntity];
        }

        $query = UserRegulationRepository::GetUnfilledRequiredRegulationsQuery($questionary, $application);
        $unfilledRequiredRegulations = $query
            ->andWhere(['regulation.related_entity' => $relatedEntity])
            ->all();

        if ($unfilledRequiredRegulations && $application) {
            $conditionalRegulationsRelatedList = RegulationRelationManager::GetRelatedListWithDependentNotEmptyAbstractionForApplication();
            $regulationsRelatedKeysList = array_keys($conditionalRegulationsRelatedList);
            $unfilledRequiredRegulations = array_filter($unfilledRequiredRegulations, function (Regulation $regulation) use ($application, $conditionalRegulationsRelatedList, $regulationsRelatedKeysList) {
                if (!in_array($regulation->related_entity, $regulationsRelatedKeysList)) {
                    return true;
                }
                $functionName = $conditionalRegulationsRelatedList[$regulation->related_entity];

                if ($application->{$functionName}()->exists()) {
                    return true;
                }
                return false;
            });
        }


        return !$unfilledRequiredRegulations;
    }

    





    public static function GetRegulationsWithEmptyFile(AbiturientQuestionary $questionary, $relatedEntity, BachelorApplication $application = null): array
    {
        $query = UserRegulation::find()
            ->joinWith(['regulation r' => function ($q) {
                $q->joinWith('attachmentType at');
            }])
            ->joinWith('attachments')
            ->andWhere([
                'user_regulation.owner_id' => $questionary->user_id,
                'r.related_entity' => $relatedEntity,
                'at.required' => true,
            ])
            ->andWhere(['not', ['r.attachment_type' => null]])
            ->andWhere([Attachment::tableName() . '.id' => null]);

        if ($application) {
            $query->andWhere([
                'or',
                ['user_regulation.abiturient_questionary_id' => $questionary->id],
                ['user_regulation.application_id' => $application->id]
            ]);
        } else {
            $query->andWhere([
                'user_regulation.abiturient_questionary_id' => $questionary->id
            ]);
        }

        return $query->all();
    }


    





    public static function GetAllUserRegulationsByRelatedEntity(AbiturientQuestionary $questionary, $relatedEntity, BachelorApplication $application): array
    {
        if (!is_array($relatedEntity)) {
            $relatedEntity = [$relatedEntity];
        }

        $userRegulationTable = UserRegulation::tableName();
        $query = UserRegulation::find()
            ->joinWith('regulation r')
            ->where([
                'r.related_entity' => $relatedEntity,
            ])
            ->andWhere(["{$userRegulationTable}.owner_id" => $questionary->user_id])
            ->andWhere([
                'or',
                ["{$userRegulationTable}.abiturient_questionary_id" => $questionary->id],
                ["{$userRegulationTable}.application_id" => $application->id],
            ]);

        return $query->all();
    }
}
