<?php

namespace common\modules\abiturient\models;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\Country;
use common\models\dictionary\Fias;
use common\models\EmptyCheck;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\interfaces\IQuestionnaireValidateModelInterface;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;





































class AddressData extends ChangeHistoryDecoratedModel implements
    QuestionaryConnectedInterface,
    ChangeLoggedModelInterface,
    IQuestionnaireValidateModelInterface,
    IHaveIdentityProp,
    ICanGivePropsToCompare
{
    use HasDirtyAttributesTrait;
    use HtmlPropsEncoder;

    public const ADDRESS_TYPE_REGISTRATION = 1;

    public const ADDRESS_TYPE_ACTUAL = 2;

    public const SCENARIO_GET_ANKETA = 'get_anketa';

    public const SCENARIO_NOT_REQUIRED = 'not_required';

    public static function tableName()
    {
        return '{{%address_data}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public $isFrom1C = false;

    


    public function rules()
    {
        return [
            [
                [
                    'city_name',
                    'area_name',
                    'town_name',
                    'street_name',
                    'region_name',
                ],
                'trim'
            ],
            [
                [
                    'country_id',
                    'address_type',
                    'questionary_id',
                ],
                'integer'
            ],
            [
                [
                    'homeless',
                    'not_found',
                ],
                'boolean'
            ],
            [
                [
                    'area_id',
                    'city_id',
                    'region_id',
                    'street_id',
                    'kladr_code',
                    'village_id',
                    'flat_number',
                    'house_number',
                    'postal_index',
                    'housing_number',
                ],
                'string',
                'max' => 100
            ],
            [
                [
                    'city_name',
                    'area_name',
                    'town_name',
                    'street_name',
                    'region_name',
                ],
                'string',
                'max' => 255
            ],
            [
                ['questionary_id'],
                'required'
            ],
            [
                [
                    'homeless',
                    'not_found',
                ],
                'default',
                'value' => false
            ],
            [
                [
                    'region_name',
                    'postal_index',
                    'house_number',
                ],
                'required',
                'when' => function ($model) {
                    return self::requiredRule($model, false);
                },
                'whenClient' => '
                    function (attribute, value) {
                        return window.requiredRule(attribute, "' . Yii::$app->configurationManager->getCode('russia_guid') . '", false);
                    }
                ',
                'except' => [self::SCENARIO_NOT_REQUIRED]
            ],
            [
                [
                    'country_id',
                    'postal_index',
                    'house_number',
                    'questionary_id',
                ],
                'required',
                'when' => function ($model) {
                    return self::requiredRule($model, true);
                },
                'except' => [self::SCENARIO_GET_ANKETA, self::SCENARIO_NOT_REQUIRED],
                'whenClient' => '
                    function (attribute, value) {
                        return window.requiredRule(attribute, "' . Yii::$app->configurationManager->getCode('russia_guid') . '", true);
                    }
                ',
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_GET_ANKETA] = [
            'questionary_id',
            'street_id',
            'kladr_code',
            'country_id',
            'postal_index',
            'house_number',
        ];
        $scenarios[self::SCENARIO_NOT_REQUIRED] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }


    


    public function attributeLabels()
    {
        return [
            'area_id' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "area_id" формы "Данные адреса": `Район`'),
            'city_id' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "city_id" формы "Данные адреса": `Город`'),
            'areaName' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "areaName" формы "Данные адреса": `Район`'),
            'cityName' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "cityName" формы "Данные адреса": `Город`'),
            'homeless' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "homeless" формы "Данные адреса": `Без определенного места жительства`'),
            'townName' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "townName" формы "Данные адреса": `Населенный пункт`'),
            'area_name' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "area_name" формы "Данные адреса": `Район`'),
            'city_name' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "city_name" формы "Данные адреса": `Город`'),
            'not_found' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "not_found" формы "Данные адреса": `Не нашёл свой адрес в адресном классификаторе`'),
            'region_id' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "region_id" формы "Данные адреса": `Регион`'),
            'street_id' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "street_id" формы "Данные адреса": `Улица`'),
            'town_name' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "town_name" формы "Данные адреса": `Населенный пункт`'),
            'country_id' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "country_id" формы "Данные адреса": `Страна`'),
            'kladr_code' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "kladr_code" формы "Данные адреса": `Код КЛАДР`'),
            'regionName' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "regionName" формы "Данные адреса": `Регион`'),
            'streetName' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "streetName" формы "Данные адреса": `Улица`'),
            'village_id' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "village_id" формы "Данные адреса": `Населенный пункт`'),
            'countryName' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "countryName" формы "Данные адреса": `Страна`'),
            'flat_number' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "flat_number" формы "Данные адреса": `Квартира`'),
            'region_name' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "region_name" формы "Данные адреса": `Регион`'),
            'street_name' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "street_name" формы "Данные адреса": `Улица`'),
            'house_number' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "house_number" формы "Данные адреса": `Дом`'),
            'postal_index' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "postal_index" формы "Данные адреса": `Индекс`'),
            'housing_number' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "housing_number" формы "Данные адреса": `Корпус`'),
            'questionary_id' => Yii::t('abiturient/questionary/address-data', 'Подпись для поля "questionary_id" формы "Данные адреса": `Анкета`'),
        ];
    }

    





    private static function requiredRule($model, $checkIfFount)
    {
        $notFoundValue = 1;
        if ($checkIfFount) {
            $notFoundValue = 0;
        }

        return !$model->isFrom1C &&
            $model->homeless != 1 &&
            $model->not_found == $notFoundValue &&
            !empty($model->country->ref_key) &&
            $model->country->ref_key == Yii::$app->configurationManager->getCode('russia_guid');
    }

    public function isPostalIndexRequired(): bool
    {
        return static::requiredRule($this, true);
    }

    public function cleanUnusedAttributes()
    {
        if ($this->homeless) {
            $this->area_id = null;
            $this->area_name = null;
            $this->city_id = null;
            $this->city_name = null;
            $this->country_id = null;
            $this->flat_number = null;
            $this->house_number = null;
            $this->housing_number = null;
            $this->kladr_code = null;
            $this->postal_index = null;
            $this->region_id = null;
            $this->region_name = null;
            $this->street_id = null;
            $this->street_name = null;
            $this->town_name = null;
            $this->village_id = null;
        }
        if (
            $this->not_found != 1 &&
            $this->country != null &&
            $this->country->ref_key == Yii::$app->configurationManager->getCode('russia_guid')
        ) {
            $this->area_name = null;
            $this->city_name = null;
            $this->region_name = null;
            $this->street_name = null;
            $this->town_name = null;
        } elseif (
            $this->country != null &&
            $this->country->ref_key != Yii::$app->configurationManager->getCode('russia_guid')
        ) {
            $this->area_id = null;
            $this->area_name = null;
            $this->city_id = null;
            $this->kladr_code = null;
            $this->region_id = null;
            $this->region_name = null;
            $this->street_id = null;
            $this->town_name = null;
            $this->village_id = null;
        } elseif (
            $this->not_found &&
            $this->country != null &&
            $this->country->ref_key == Yii::$app->configurationManager->getCode('russia_guid')
        ) {
            $this->area_id = null;
            $this->city_id = null;
            $this->kladr_code = null;
            $this->region_id = null;
            $this->street_id = null;
            $this->village_id = null;
        }
    }

    public function getAbiturientQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
    }

    public function getRegion()
    {
        $entry = $this->hasOne(Fias::class, ['code' => 'region_id']);
        if ($entry->one() === null) {
            $resovingStatus = $this->resolveKladrCode('region_id');
            if ($resovingStatus) {
                $entry = $this->hasOne(Fias::class, ['code' => 'region_id']);
            }
        }

        return $entry;
    }

    public function getArea()
    {
        $entry = $this->hasOne(Fias::class, ['code' => 'area_id']);
        if ($entry->one() === null) {
            $resovingStatus = $this->resolveKladrCode('area_id');
            if ($resovingStatus) {
                $entry = $this->hasOne(Fias::class, ['code' => 'area_id']);
            }
        }

        return $entry;
    }

    public function getCity()
    {
        $entry = $this->hasOne(Fias::class, ['code' => 'city_id']);
        if ($entry->one() === null) {
            $resovingStatus = $this->resolveKladrCode('city_id');
            if ($resovingStatus) {
                $entry = $this->hasOne(Fias::class, ['code' => 'city_id']);
            }
        }

        return $entry;
    }

    public function getVillage()
    {
        $entry = $this->hasOne(Fias::class, ['code' => 'village_id']);
        if ($entry->one() === null) {
            $resovingStatus = $this->resolveKladrCode('village_id');
            if ($resovingStatus) {
                $entry = $this->hasOne(Fias::class, ['code' => 'village_id']);
            }
        }

        return $entry;
    }

    public function getStreet()
    {
        return $this->hasOne(Fias::class, ['code' => 'street_id']);
    }

    





    protected function getNormalKladrCode()
    {
        $string = $this->kladr_code;
        if (strlen((string)$string) < 17) {
            $different = 17 - strlen((string)$string);
            for ($i = 0; $i < $different; $i++) {
                $string .= '0';
            }
        }

        return $string;
    }

    







    public function resolveKladrCode($attr)
    {
        $attributeList = [
            'region_id',
            'area_id',
            'city_id',
            'village_id'
        ];
        if (!in_array($attr, $attributeList)) {
            return false;
        }
        $code = $this->$attr;
        $len = strlen((string)$code);
        $kladrEntity = Fias::findOne([
            'code' => $code
        ]);

        if ($kladrEntity !== null) {
            return true;
        }
        $trialCode = '';
        if ($len === 17) {
            $trialCode = substr($code, 0, $len - 4);
            $kladrEntity = Fias::findOne([
                'code' => $trialCode
            ]);
        } elseif ($len === 13) {
            $different = 17 - $len;
            $trialCode = $code;
            for ($i = 0; $i < $different; $i++) {
                $trialCode .= '0';
            }
        }

        if ($kladrEntity !== null) {
            $this->$attr = $trialCode;
            if ($this->validate()) {
                $this->save(false);
            } else {
                Yii::error(
                    'Ошибка при попытке восстановить код КЛАДР',
                    'ADDRESS_KLADR_RESOLVING'
                );
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'region_id' => function ($model) {
                return ArrayHelper::getValue($model, 'region.name');
            },
            'area_id' => function ($model) {
                return ArrayHelper::getValue($model, 'area.name');
            },
            'city_id' => function ($model) {
                return ArrayHelper::getValue($model, 'city.name');
            },
            'street_id' => function ($model) {
                return ArrayHelper::getValue($model, 'street.name');
            },
            'village_id' => function ($model) {
                return ArrayHelper::getValue($model, 'village.name');
            },
            'house_number',
            'housing_number',
            'flat_number',
            'postal_index',
            'street_name',
            'city_name',
            'region_name',
            'area_name',
            'town_name',
            'country_id' => function ($model) {
                return ArrayHelper::getValue($model, 'country.name');
            },
            'homeless' => function ($model) {
                return $model->homeless ? 'Да' : 'Нет';
            }
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_ADDRESS_DATA;
    }

    public static function find()
    {
        return parent::find()->where([
            'address_type' => self::ADDRESS_TYPE_REGISTRATION
        ]);
    }

    


    public function getAddressTypeComputed(): int
    {
        return self::ADDRESS_TYPE_REGISTRATION;
    }

    






    public function beforeSave($insert)
    {
        $status = parent::beforeSave($insert);

        $this->address_type = $this->getAddressTypeComputed();

        return $status;
    }

    public static function setAddressProperty(
        &$addressData,
        $fias_elem,
        $fias_name_string,
        $id_column,
        $name_column,
        $set_not_found = true,
        $set_homeless = false
    )
    {
        if (empty($fias_elem) && empty($fias_name_string)) {
            $addressData->{$id_column} = null;
            $addressData->{$name_column} = null;
        } else {
            if (!empty($fias_elem)) {
                $addressData->{$id_column} = $fias_elem->code;
                $addressData->{$name_column} = $fias_elem->getFullname();
                if ($set_not_found) {
                    $addressData->not_found = false;
                }
            } else {
                if ($set_not_found) {
                    $addressData->not_found = true;
                }
                $addressData->{$id_column} = null;
                $addressData->{$name_column} = $fias_name_string;
                if ($set_homeless && EmptyCheck::isEmpty($addressData->{$name_column})) {
                    $addressData->homeless = true;
                }
            }
        }
    }

    public function getValidatedName(): string
    {
        return Yii::t(
            'abiturient/questionary/address-data',
            'Валидационное имя модели: `Данные об адресе регистрации`'
        );
    }

    public function formNameForJs()
    {
        return mb_strtolower($this->formName());
    }

    public function getCountryName()
    {
        return $this->country ? $this->country->name : '';
    }

    public function getRegionName()
    {
        return $this->region ? $this->region->fullname : $this->region_name;
    }

    public function getAreaName()
    {
        return $this->area ? $this->area->fullname : $this->area_name;
    }

    public function getCityName()
    {
        return $this->city ? $this->city->fullname : $this->city_name;
    }

    public function getTownName()
    {
        return $this->village ? $this->village->fullname : $this->town_name;
    }

    public function getStreetName()
    {
        return $this->street ? $this->street->fullname : $this->street_name;
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    public function processKLADRCode(): self
    {
        if (!empty($this->street_id)) {
            $this->kladr_code = $this->street_id;

            return $this;
        }

        if (!empty($this->city_id)) {
            $this->kladr_code = $this->city_id;

            return $this;
        }

        if (!empty($this->village_id)) {
            $this->kladr_code = $this->village_id;

            return $this;
        }

        if (!empty($this->area_id)) {
            $this->kladr_code = $this->area_id;

            return $this;
        }

        return $this;
    }

    public function showRequiredSymbols(): bool
    {
        return $this->scenario != AddressData::SCENARIO_NOT_REQUIRED;
    }

    public function getIdentityString(): string
    {
        return $this->address_type;
    }

    public function getPropsToCompare(): array
    {
        return [
            'countryName',
            'regionName',
            'areaName',
            'cityName',
            'townName',
            'streetName',
            'house_number',
            'housing_number',
            'flat_number',
            'postal_index',
            'homeless',
        ];
    }
}
