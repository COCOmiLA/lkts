<?php


namespace common\components\AddressWidget;


use common\components\AddressHelper\AddressHelper;
use common\components\AddressHelper\AddressQueryBuilder;
use common\models\dictionary\Country;
use common\models\dictionary\Fias;
use common\modules\abiturient\models\AddressData;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class AddressWidget extends Widget
{
    


    public $addressData;

    public $isReadonly;

    public $disabled;

    public $prefix;

    public $form;

    public $template;
    public $comparison_helper;

    



    protected static $data_for_js = [];

    public function run()
    {
        $address_data = $this->addressData;

        $countryGuid = Yii::$app->configurationManager->getCode('russia_guid');

        $country = Country::findOne(['ref_key' => $countryGuid, 'archive' => false]);
        $countryId = empty($country) ? null : $country->id;

        $countries = Country::find()->notMarkedToDelete()->active()->orderBy('name')->all();
        $countryCodes = ArrayHelper::map($countries, 'id', 'datacode');
        $countriesList = ArrayHelper::map($countries, 'id', 'name');

        if ($address_data->country_id && !isset($countriesList[$address_data->country_id])) {
            $chosen_country = Country::find()->where(['id' => $address_data->country_id])->one();
            if ($chosen_country) {
                $countryCodes[$chosen_country->id] = $chosen_country->datacode;
                $countriesList[$chosen_country->id] = $chosen_country->name;
            }
        }
        if ($address_data->country_id == null) {
            $address_data->country_id = $countryId;
        }

        $region_selected = Fias::find()->where([
            'code' => $address_data->region_id
        ])->all();

        $area_selected = Fias::find()->where([
            'code' => $address_data->area_id,
        ])->all();

        $city_selected = Fias::find()->where([
            'code' => $address_data->city_id,
        ])->all();

        $village_selected = Fias::find()->where([
            'code' => $address_data->village_id,
        ])->all();

        return $this->render(
            '_addressForm',
            [
                'address_data' => $address_data,
                'countriesList' => $countriesList,
                'disabled' => $this->disabled,
                'form' => $this->form,
                'isReadonly' => $this->isReadonly,
                'prefix' => $this->prefix,
                'template' => $this->template,
                'countryCodes' => $countryCodes,
                'comparison_helper' => $this->comparison_helper,
                'region_selected' => $region_selected,
                'area_selected' => $area_selected,
                'city_selected' => $city_selected,
                'village_selected' => $village_selected,
            ]
        );
    }

    public static function addDataForJsItem(array $item)
    {
        static::$data_for_js[] = $item;
    }

    public static function registerJsVarForInitialization(string $varName = "addressWidgetDataForInitialization")
    {
        \Yii::$app->view->registerJsVar($varName, AddressWidget::$data_for_js);
    }
}
