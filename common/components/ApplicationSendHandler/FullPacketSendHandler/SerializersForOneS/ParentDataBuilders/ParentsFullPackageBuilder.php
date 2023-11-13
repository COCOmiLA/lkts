<?php

namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ParentDataBuilders;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BaseQuestionaryPackageBuilder;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\FamilyType;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\parentData\ParentData;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class ParentsFullPackageBuilder extends BaseQuestionaryPackageBuilder
{
    
    protected $application;

    public function __construct(AbiturientQuestionary $questionary, ?BachelorApplication $application = null)
    {
        parent::__construct($questionary);
        $this->application = $application;
    }

    protected bool $allow_direct_fetching = false;

    public function setAllowDirectFetching(bool $allow_direct_fetching): ParentsFullPackageBuilder
    {
        $this->allow_direct_fetching = $allow_direct_fetching;
        return $this;
    }

    public function build()
    {
        $result = [];

        $parent_data = $this->questionary->parentData;
        if ($parent_data) {
            ParentData::checkInterfaceVersion('PostEntrantPackage');
            foreach ($parent_data as $parent) {
                $result[] = [
                    'RelationDegreeRef' => ReferenceTypeManager::GetReference($parent, 'type'),
                    'RelativePersonalData' => (new ParentPersonalDataFullPackageBuilder($this->questionary, $parent, $this->application))
                        ->setFilesSyncer($this->files_syncer)
                        ->build(),
                    'ParentRef' => ReferenceTypeManager::GetReference($parent, 'parentRef', StoredUserReferenceType::getReferenceClassName()),
                ];
            }
        }

        return $result;
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

        $questionary = $this->questionary;

        $touched_ids = [];
        foreach ($raw_data as $raw_parent) {
            $parent_ref_raw = ArrayHelper::getValue($raw_parent, 'ParentRef');
            if ($parent_ref_raw instanceof \SoapVar) {
                $parent_ref_raw = $parent_ref_raw->enc_value;
            }

            $parent_ref = ReferenceTypeManager::GetOrCreateReference(StoredUserReferenceType::class, $parent_ref_raw);
            $parent_data = null;
            
            if ($parent_ref) {
                $parent_data = ParentData::find()
                    ->andWhere([ParentData::tableName() . '.questionary_id' => $questionary->id])
                    ->joinWith('parentRef parentRef')
                    ->andWhere(['parentRef.reference_uid' => ArrayHelper::getValue($parent_ref, 'reference_uid')])
                    ->one();
            }

            
            if ($parent_data === null) {
                $parent_data = ParentData::find()
                    ->joinWith('personalData personalData')
                    ->andWhere([ParentData::tableName() . '.questionary_id' => $questionary->id])
                    ->andWhere(['personalData.firstname' => ArrayHelper::getValue($raw_parent, 'RelativePersonalData.Name')])
                    ->andWhere(['personalData.lastname' => ArrayHelper::getValue($raw_parent, 'RelativePersonalData.Surname')])
                    ->andFilterWhere(['personalData.middlename' => ArrayHelper::getValue($raw_parent, 'RelativePersonalData.Patronymic')])
                    ->andWhere(['personalData.birthdate' => date('d.m.Y', strtotime(ArrayHelper::getValue($raw_parent, 'RelativePersonalData.Birthday')))])
                    ->one();
            }

            
            if ($parent_data === null) {
                $parent_data = new ParentData();
                $parent_data->questionary_id = $questionary->id;
            }

            $parent_data->archive = false;
            $parent_data->parent_ref_id = ArrayHelper::getValue($parent_ref, 'id');
            $parent_data->code = ArrayHelper::getValue($parent_ref, 'reference_id');

            $type_ref = ReferenceTypeManager::GetOrCreateReference(
                FamilyType::class,
                ArrayHelper::getValue($raw_parent, 'RelationDegreeRef')
            );
            $parent_data->type_id = ArrayHelper::getValue($type_ref, 'id');

            $state = (new ParentPersonalDataFullPackageBuilder($this->questionary, $parent_data, $this->application))
                ->setFilesSyncer($this->files_syncer)
                ->setAllowDirectFetching($this->allow_direct_fetching)
                ->update(ArrayHelper::getValue($raw_parent, 'RelativePersonalData'));
            if (!$state) {
                throw new UserException('Не удалось обновить данные из Информационной системы вуза в блоке персональных данных родителей или законных представителей');
            }

            if (!$parent_data->validate()) {
                throw new UserException(get_class($parent_data) . PHP_EOL . print_r($parent_data->errors, true));
            }

            if (!$parent_data->save(false)) {
                throw new UserException('Ошибка сохранения ' . get_class($parent_data));
            }
            $touched_ids[] = $parent_data->id;
        }
        
        $parents_to_delete = ParentData::find()
            ->andWhere(['questionary_id' => $questionary->id])
            ->andFilterWhere(['not', [ParentData::tableName() . '.id' => $touched_ids]])
            ->all();

        foreach ($parents_to_delete as $parent) {
            $parent->archive(false);
        }

        return true;
    }
}
