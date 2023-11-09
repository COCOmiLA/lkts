<?php

namespace common\modules\abiturient\models\parentData;

use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\FreePassportData;
use common\modules\abiturient\models\PassportData;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use common\modules\abiturient\validators\extenders\ParentPassportData\ParentPassportDataValidation;





class ParentPassportData extends FreePassportData
{
    


    protected $userForInitialization;
    
    public $validation_extender;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->validation_extender = new ParentPassportDataValidation([
            'model' => $this
        ]);
        $this->validation_extender->modelPreparationCallback();
    }
    
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), $this->validation_extender->getRules());
    }
    
    


    public function getParentData()
    {
        return $this->hasOne(ParentData::class, ['passport_data_id' => 'id'])->andWhere(['archive' => false]);
    }

    public function setUserForInitialization(User $user)
    {
        $this->userForInitialization = $user;
    }

    public function getAbiturientQuestionary()
    {
        
        
        
        if ($this->parentData === null && isset($this->userForInitialization)) {
            return $this->userForInitialization->getAbiturientQuestionary();
        }

        
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id'])
            ->viaTable('parent_data', ['passport_data_id' => 'id']);
    }

    public function getEntityIdentifier(): ?string
    {
        return Yii::t(
            'abiturient/questionary/parent-passport-data',
            'Идентификатор модели: `Паспортные данные родителя или законного представителя`'
        );
    }
    
    protected static function setQuestionaryLink(PassportData $model, ?int $questionary_id)
    {
        return; 
    }
    
    public function setScenarioForUpdateFromRaw()
    {
        $this->validation_extender->modelPreparationCallback();
    }
}
