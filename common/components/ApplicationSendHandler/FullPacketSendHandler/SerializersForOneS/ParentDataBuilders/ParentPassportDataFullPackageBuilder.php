<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ParentDataBuilders;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\PassportsFullPackageBuilder;
use common\components\queries\ArchiveQuery;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\parentData\ParentPassportData;
use common\modules\abiturient\models\PersonalData;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class ParentPassportDataFullPackageBuilder extends PassportsFullPackageBuilder
{
    private $parent_data;

    public function __construct(AbiturientQuestionary $questionary, ParentData $parent_data, ?BachelorApplication $application = null)
    {
        parent::__construct($questionary, $application);
        $this->parent_data = $parent_data;
    }

    public function getPersonalData(): PersonalData
    {
        return $this->parent_data->personalData;
    }

    public function getPassportData($all = false): ArchiveQuery
    {
        return $this->parent_data->getPassportData();
    }

    public function update($raw_data): bool
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);
        if (empty($raw_data)) {
            $raw_data = [];
        }
        if (!is_array($raw_data) || ArrayHelper::isAssociative($raw_data)) {
            $raw_data = [$raw_data];
        }
        $touched_ids = [];

        foreach ($raw_data as $raw_passport) {
            if (
                empty($raw_passport)
                || empty(ArrayHelper::getValue($raw_passport, 'Document.DocumentTypeRef'))
                || ReferenceTypeManager::isReferenceTypeEmpty(ArrayHelper::getValue($raw_passport, 'Document.DocumentTypeRef'))
            ) {
                continue;
            }
            $local_pass = null;
            try {
                $local_pass = ParentPassportData::GetOrCreateFromRaw(
                    $this->parent_data,
                    ArrayHelper::getValue($raw_passport, 'Document.DocSeries'),
                    ArrayHelper::getValue($raw_passport, 'Document.DocNumber'),
                    ArrayHelper::getValue($raw_passport, 'Document.IssueDate'),
                    ArrayHelper::getValue($raw_passport, 'Document.DocOrganization'),
                    ArrayHelper::getValue($raw_passport, 'Document.DocumentTypeRef'),
                    $touched_ids
                );
            } catch (\Throwable $e) {
                throw new UserException("Не удалось сохранить паспортные данные родителя/законного представителя " . $e->getMessage());
            }
            $touched_ids[] = $local_pass->id;
            $this->parent_data->passport_data_id = $local_pass->id;
        }

        return true;
    }
}
