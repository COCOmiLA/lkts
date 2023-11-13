<?php

namespace common\modules\abiturient\models;

use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\validators\extenders\ActualAddressData\ActualAddressAppsCheckValidation;
use Yii;
use yii\helpers\ArrayHelper;







class ActualAddressData extends AddressData
{
    
    public $sameAsRegistrationAddress;

    public $validation_extender;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->validation_extender = new ActualAddressAppsCheckValidation([
            'model' => $this
        ]);
    }


    public function formName()
    {
        return 'ActualAddressData';
    }

    public static function find()
    {
        return parent::find()->where([
            'address_type' => self::ADDRESS_TYPE_ACTUAL
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), $this->validation_extender ? $this->validation_extender->getRules() : []);
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_ACTUAL_ADDRESS_DATA;
    }

    public function getAddressTypeComputed(): int
    {
        return self::ADDRESS_TYPE_ACTUAL;
    }

    public function getValidatedName(): string
    {
        return Yii::t(
            'abiturient/questionary/address-data',
            'Валидационное имя модели: `Данные о адресе проживания`'
        );
    }
}
