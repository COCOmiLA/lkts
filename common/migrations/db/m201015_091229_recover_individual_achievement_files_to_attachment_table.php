<?php

use common\components\AttachmentManager;
use common\components\ini\iniSet;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\Attachment;
use common\models\AttachmentType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\IndividualAchievement;
use yii\db\ActiveQuery;




class m201015_091229_recover_individual_achievement_files_to_attachment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        iniSet::disableTimeLimit();

        try {
            $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
        foreach (IndividualAchievement::find()->all() as $ind) {
            $application = $this->getApplication($ind);
            if ($application === null) {
                Yii::error("При восстановлении документов индивидуальных достижений произошла ошибка. Невозможно найти заявление к которому привязано индивидуальное достижение. ID: {$ind->id}", 'FILE_RECOVER');

                continue;
            }
            if ($ind->file !== null) {
                $attachment = new Attachment();
                $attachment->setScenario(Attachment::SCENARIO_RECOVER);
                $attachment->attachment_type_id = $type->id;
                $attachment->file = $ind->file;
                $attachment->filename = $ind->filename;
                $attachment->deleted = false;

                if (\common\models\User::find()->where(['id' => $ind->user_id])->exists()) {
                    $attachment->owner_id = $ind->user_id;
                }

                $application = $this->getApplication($ind);
                if ($application !== null) {
                    $attachment->application_id = $application->id;
                }

                if ($attachment->save()) {
                    AttachmentManager::linkAttachment($ind, $attachment);
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    private function getApplication($ia)
    {
        if ($ia->achievementType === null) {
            return null;
        }
        $admissionCampaignRef = $ia->achievementType->admissionCampaignRef;
        $app = BachelorApplication::find()
            ->joinWith(['type' => function (ActiveQuery $q) use ($admissionCampaignRef) {
                $q->joinWith(['campaign.referenceType campaign_ref'])
                    ->andWhere([
                        'campaign_ref.reference_uid' => $admissionCampaignRef->reference_uid
                    ]);
            }])
            ->andWhere([
                'bachelor_application.user_id' => $ia->user_id
            ])->one();
        if (!$app) {
            $campaign_code = $ia->achievementType->campaign_code;
            $app = BachelorApplication::find()
                ->joinWith(['type' => function (ActiveQuery $q) use ($campaign_code) {
                    $q->joinWith(['campaign campaign'])
                        ->andWhere([
                            'campaign.code' => $campaign_code
                        ]);
                }])
                ->andWhere([
                    'bachelor_application.user_id' => $ia->user_id
                ])->one();
        }
        return $app;
    }
}
