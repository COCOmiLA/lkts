<?php

namespace common\modules\abiturient\models;




class FreePassportData extends PassportData
{
    


    public function rules()
    {
        $rules = parent::rules();
        
        foreach ($rules as $index => $rule) {
            if ($rule[1] === 'required') {
                $props = is_array($rule[0]) ? $rule[0] : [$rule[0]];
                $attrIndex = array_search('questionary_id', $props);
                if ($attrIndex !== false) {
                    unset($rules[$index][0][$attrIndex]);
                }
            }
        }
        
        return $rules;
    }
}
