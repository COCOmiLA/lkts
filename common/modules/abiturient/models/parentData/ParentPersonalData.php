<?php

namespace common\modules\abiturient\models\parentData;

use common\components\ReferenceTypeManager\exceptions\ReferenceManagerBadGetReferenceRequest;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\soapException;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\FreePersonalData;
use common\modules\abiturient\validators\extenders\PersonalData\ParentPersonalDataValidation;
use Yii;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;





class ParentPersonalData extends FreePersonalData
{
    


    protected $userForInitialization;

    
    public $validation_extender;
    
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->validation_extender = new ParentPersonalDataValidation([
            'model' => $this
        ]);
    }
    
    public function rules()
    {
        $base_rules = parent::rules();
        return ArrayHelper::merge($base_rules, $this->validation_extender ? $this->validation_extender->getRules() : []);
    }
    
    


    public function getParentData()
    {
        return $this->hasOne(ParentData::class, ['personal_data_id' => 'id'])->andWhere(['archive' => false]);
    }

    


    public function getEmail()
    {
        return $this->parentData->email;
    }

    








    public function getEntrantRef()
    {
        return UserReferenceTypeManager::GetProcessedUserReferenceType($this->parentData->abiturientQuestionary->user);
    }

    public function setUserForInitialization(User $user)
    {
        $this->userForInitialization = $user;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->changeHistoryHandler
                ->getInsertHistoryAction()
                ->proceed();
        } else {
            $this->changeHistoryHandler
                ->getUpdateHistoryAction()
                ->proceed();
        }
    }

    public function getEntityIdentifier(): ?string
    {
        return Yii::t(
            'abiturient/questionary/parent-personal-data',
            'Идентификатор модели: `Персональные данные родителя или законного представителя`'
        );
    }

    public function getAbiturientQuestionary()
    {
        
        
        
        if ($this->parentData === null && isset($this->userForInitialization)) {
            return $this->userForInitialization->getAbiturientQuestionary();
        }

        
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id'])
            ->viaTable('parent_data', ['personal_data_id' => 'id']);
    }
}
