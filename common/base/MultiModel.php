<?php

namespace common\base;

use yii\base\Model;
use yii\helpers\ArrayHelper;

















class MultiModel extends Model
{
    


    public $db = 'db';
    


    protected $models = [];

    




    public function setModel($key, Model $model)
    {
        return $this->models[$key] = $model;
    }

    


    public function setModels(array $models)
    {
        foreach ($models as $key => $model) {
            $this->setModel($key, $model);
        }
    }

    



    public function getModel($key)
    {
        return ArrayHelper::getValue($this->models, $key, false);
    }

    


    public function getModels()
    {
        return $this->models;
    }

    




    public function load($data, $formName = '')
    {
        foreach ($this->models as $k => &$model) {
            $success = $model->load($data);
            if (!$success) {
                return false;
            }
        }
        return true;
    }

    




    public function validate($attributeNames = null, $clearErrors = true)
    {
        $this->trigger(Model::EVENT_BEFORE_VALIDATE);
        $success = true;
        foreach ($this->models as $key => $model) {
            
            if (!$model->validate()) {
                $success = false;
                $this->addErrors([$key => $model->getErrors()]);
            }
        }
        $this->trigger(Model::EVENT_AFTER_VALIDATE);
        return $success;
    }

    




    public function save($runValidation = true)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }
        $success = true;
        $transaction = $this->getDb()->beginTransaction();
        foreach ($this->models as $model) {
            if (!$success) {
                $transaction->rollBack();
                return false;
            }
            $success = $model->save(false);
        }
        $transaction->commit();
        return $success;
    }

    


    public function getDb()
    {
        return \Yii::$app->get($this->db);
    }
}
