<?php

namespace common\modules\abiturient\models\parentData;

use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\FreeAddressData;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use common\modules\abiturient\validators\extenders\ParentAddressData\ParentAddressDataValidation;





class ParentAddressData extends FreeAddressData
{
    
    protected $userForInitialization;

    
    public $validation_extender;
    
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->validation_extender = new ParentAddressDataValidation([
            'model' => $this
        ]);
        $this->validation_extender->modelPreparationCallback();
    }
    
    public function getInputPrefix()
    {
        return 'parentActual_' . ($this->isNewRecord ? 0 : $this->id) . '_';
    }

    public function rules()
    {
        $rules = parent::rules();

        foreach ($rules as $index => $rule) {
            if ($rule[1] === 'required') {
                $rules[$index]['whenClient'] = 'function(attribute, value) { return false; }';
            }
        }

        return ArrayHelper::merge($rules, $this->validation_extender->getRules());
    }

    


    public function getParentData()
    {
        return $this->hasOne(ParentData::class, ['address_data_id' => 'id'])->andWhere(['archive' => false]);
    }

    public function setUserForInitialization(User $user)
    {
        $this->userForInitialization = $user;
    }

    public function getEntityIdentifier(): ?string
    {
        return Yii::t(
            'abiturient/questionary/parent-address-data',
            'Идентификатор модели: `Адрес регистрации родителя или законного представителя`'
        );
    }

    public function getAbiturientQuestionary()
    {
        
        
        
        if ($this->parentData === null && isset($this->userForInitialization)) {
            return $this->userForInitialization->getAbiturientQuestionary();
        }

        
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id'])
            ->viaTable('parent_data', ['address_data_id' => 'id']);
    }
}
