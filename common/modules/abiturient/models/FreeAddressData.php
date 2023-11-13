<?php

namespace common\modules\abiturient\models;

use Yii;




class FreeAddressData extends AddressData
{
    


    public function rules()
    {
        $rules = parent::rules();
        
        foreach ($rules as $index => $rule) {
            if ($rule[1] === 'required') {
                $attrIndex = array_search('questionary_id', $rule[0]);
                if ($attrIndex !== false) {
                    unset($rules[$index][0][$attrIndex]);
                }
            }
        }
        
        return $rules;
    }
    
    public function processAddressDataFromPost()
    {
        if ($this->area_id == "null") {
            $this->area_id = null;
        }

        $this->cleanUnusedAttributes();
        if ($this->country != null && $this->country->ref_key != Yii::$app->configurationManager->getCode('russia_guid')) {
            $this->not_found = true;
        }

        $this->processKLADRCode();
    }
}
