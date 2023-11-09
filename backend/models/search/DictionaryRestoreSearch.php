<?php

namespace backend\models\search;

use common\components\LikeQueryManager;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\Country;
use common\models\dictionary\DocumentType;
use common\models\dictionary\EducationType;
use common\models\dictionary\FamilyType;
use common\models\dictionary\ForeignLanguage;
use common\models\dictionary\Gender;
use common\models\dictionary\Privilege;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\SpecialRequirementReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAchievementCategoryReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredBudgetLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDetailGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDirectionReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentSetReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicKindReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicTypeReferenceType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubdivisionReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubjectSetReferenceType;
use common\models\dictionary\StoredReferenceType\StoredVariantOfRetestReferenceType;
use common\models\EmptyCheck;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class DictionaryRestoreSearch extends \yii\base\Model
{
    public $dict_index;
    public $id;
    public $actual_name;
    public $actual_uid;
    public $actual_data_version;
    public $old_id;
    public $old_name;
    public $old_uid;
    public $old_data_version;

    public $dicts = [
        StoredAdmissionCampaignReferenceType::class,
        StoredAchievementCategoryReferenceType::class,
        StoredBudgetLevelReferenceType::class,
        StoredDetailGroupReferenceType::class,
        StoredDirectionReferenceType::class,
        StoredProfileReferenceType::class,
        StoredEducationLevelReferenceType::class,
        StoredEducationFormReferenceType::class,
        StoredCompetitiveGroupReferenceType::class,
        StoredSubjectSetReferenceType::class,
        StoredDisciplineReferenceType::class,
        StoredDisciplineFormReferenceType::class,
        StoredEducationSourceReferenceType::class,
        StoredOlympicProfileReferenceType::class,
        StoredOlympicKindReferenceType::class,
        StoredOlympicLevelReferenceType::class,
        StoredOlympicTypeReferenceType::class,
        StoredVariantOfRetestReferenceType::class,
        StoredDocumentSetReferenceType::class,
        StoredSubdivisionReferenceType::class,
        StoredCurriculumReferenceType::class,
        AdmissionCategory::class,
        Country::class,
        DocumentType::class,
        EducationType::class,
        ForeignLanguage::class,
        Gender::class,
        Privilege::class,
        SpecialMark::class,
        SpecialRequirementReferenceType::class,
        FamilyType::class
    ];

    public function selectedDictClass()
    {
        return $this->dicts[$this->dict_index] ?? null;
    }

    public function getIndexedNames()
    {
        return array_map(function ($class_name) {
            return $class_name::getReferenceClassName();
        }, $this->dicts);
    }

    public function getDictionaryRecordsQuery(): ?Query
    {
        $dict_query = null;
        if (!is_null($this->dict_index)) {
            $dict_query = (new Query())
                ->from($this->selectedDictClass()::tableName())
                ->select([
                    $this->selectedDictClass()::tableName() . '.id',
                    $this->selectedDictClass()::tableName() . '.' . $this->selectedDictClass()::getNameColumnName() . ' actual_name',
                    $this->selectedDictClass()::tableName() . '.' . $this->selectedDictClass()::getUidColumnName() . ' actual_uid',
                    $this->selectedDictClass()::tableName() . '.' . $this->selectedDictClass()::getDataVersionColumnName() . ' actual_data_version',
                    'old.id old_id',
                    'old.' . $this->selectedDictClass()::getNameColumnName() . ' old_name',
                    'old.' . $this->selectedDictClass()::getUidColumnName() . ' old_uid',
                    'old.' . $this->selectedDictClass()::getDataVersionColumnName() . ' old_data_version',
                ]);
            $archive_column_name = $this->selectedDictClass()::getArchiveColumnName();
            if ($archive_column_name) {
                $dict_query->andWhere([
                    $this->selectedDictClass()::tableName() . ".{$archive_column_name}" => $this->selectedDictClass()::getArchiveColumnNegativeValue(),
                    "old.{$archive_column_name}" => $this->selectedDictClass()::getArchiveColumnPositiveValue(),
                ]);
            }
            $dict_query->innerJoin(['old' => $this->selectedDictClass()::tableName()], "old." . $this->selectedDictClass()::getUidColumnName() . ' = ' . $this->selectedDictClass()::tableName() . '.' . $this->selectedDictClass()::getUidColumnName());
            if (!EmptyCheck::isEmpty($this->id)) {
                $dict_query->andWhere([$this->selectedDictClass()::tableName() . '.id' => $this->id]);
            }
            if (!EmptyCheck::isEmpty($this->actual_name)) {
                $dict_query->andWhere([LikeQueryManager::getActionName(), $this->selectedDictClass()::tableName() . '.' . $this->selectedDictClass()::getNameColumnName(), $this->actual_name]);
            }
            if (!EmptyCheck::isEmpty($this->actual_uid)) {
                $dict_query->andWhere([LikeQueryManager::getActionName(), $this->selectedDictClass()::tableName() . '.' . $this->selectedDictClass()::getUidColumnName(), $this->actual_uid]);
            }
            if (!EmptyCheck::isEmpty($this->actual_data_version)) {
                $dict_query->andWhere([LikeQueryManager::getActionName(), $this->selectedDictClass()::tableName() . '.' . $this->selectedDictClass()::getDataVersionColumnName(), $this->actual_data_version]);
            }
            if (!EmptyCheck::isEmpty($this->old_id)) {
                $dict_query->andWhere(['old.id' => $this->old_id]);
            }
            if (!EmptyCheck::isEmpty($this->old_name)) {
                $dict_query->andWhere([LikeQueryManager::getActionName(), 'old.' . $this->selectedDictClass()::getNameColumnName(), $this->old_name]);
            }
            if (!EmptyCheck::isEmpty($this->old_uid)) {
                $dict_query->andWhere([LikeQueryManager::getActionName(), 'old.' . $this->selectedDictClass()::getUidColumnName(), $this->old_uid]);
            }
            if (!EmptyCheck::isEmpty($this->old_data_version)) {
                $dict_query->andWhere([LikeQueryManager::getActionName(), 'old.' . $this->selectedDictClass()::getDataVersionColumnName(), $this->old_data_version]);
            }
        }
        return $dict_query;
    }

    public function rules()
    {
        return [
            [[
                'id',
                'actual_name',
                'actual_uid',
                'actual_data_version',
                'old_id',
                'old_uid',
                'old_name',
                'old_data_version',
            ], 'string'],
            ['dict_index', 'number'],
        ];
    }

    public function getProvider()
    {
        $query = $this->getDictionaryRecordsQuery();

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 500,
            ]
        ]);
    }
}
