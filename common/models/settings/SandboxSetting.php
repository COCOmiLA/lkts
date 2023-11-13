<?php

namespace common\models\settings;

class SandboxSetting extends Setting
{
    


    public static function tableName()
    {
        return '{{%sandbox_settings}}';
    }
}