<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\forms;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\components\forumIn\forum\bizley\podium\src\PodiumConfig;
use yii\base\Model;
use yii\validators\StringValidator;







class ConfigForm extends Model
{
    


    public $config;

    


    public $settings;

    


    public $readonly = ['version'];

    


    public function init()
    {
        parent::init();
        $this->config = Podium::getInstance()->podiumConfig;
        $this->settings = $this->config->all;
    }

    




    public function __get($name)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : '';
    }

    




    public function update($data)
    {
        $validator = new StringValidator();
        $validator->max = 255;

        foreach ($data as $key => $value) {
            if (!in_array($key, $this->readonly) && array_key_exists($key, $this->settings)) {
                if (!$validator->validate($value)) {
                    return false;
                }
                if (!$this->config->set($key, $value)) {
                    return false;
                }
            }
        }
        return true;
    }
}
