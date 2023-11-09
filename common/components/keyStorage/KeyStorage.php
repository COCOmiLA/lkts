<?php

namespace common\components\keyStorage;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;





class KeyStorage extends Component
{
    


    public $cachePrefix = '_keyStorage';
    


    public $cachingDuration = 60;
    


    public $modelClass = \common\models\KeyStorageItem::class;

    


    private $values = [];

    




    public function set($key, $value)
    {
        $model = $this->getModel($key);
        if (!$model) {
            $model = new $this->modelClass;
            $model->key = $key;
        }
        $model->value = $value;
        if ($model->save(false)) {
            $this->values[$key] = $value;
            Yii::$app->cache->set($this->getCacheKey($key), $value, $this->cachingDuration);
            return true;
        };
        return false;
    }

    


    public function setAll(array $values)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    






    public function get($key, $default = null, $cache = true, $cachingDuration = false)
    {
        if ($cache) {
            $cacheKey = $this->getCacheKey($key);
            $value = ArrayHelper::getValue($this->values, $key, false) ?: Yii::$app->cache->get($cacheKey);
            if ($value === false) {
                if ($model = $this->getModel($key)) {
                    $value = $model->value;
                    $this->values[$key] = $value;
                    Yii::$app->cache->set(
                        $cacheKey,
                        $value,
                        $cachingDuration === false ? $this->cachingDuration : $cachingDuration
                    );
                } else {
                    $value = $default;
                }
            }
        } else {
            $model = $this->getModel($key);
            $value = $model ? $model->value : $default;
        }
        return $value;
    }

    



    public function getAll(array $keys)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }
        return $values;
    }

    



    public function has($key)
    {
        return $this->get($key) !== null;
    }

    



    public function hasAll(array $keys)
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                return false;
            }
        }
        return true;
    }

    



    public function remove($key)
    {
        unset($this->values[$key]);
        return call_user_func($this->modelClass . '::deleteAll', ['key' => $key]);
    }

    


    public function removeAll(array $keys)
    {
        foreach ($keys as $key) {
            $this->remove($key);
        }
    }

    



    protected function getModel($key)
    {
        $query = call_user_func($this->modelClass . '::find');
        return $query->where(['key' => $key])->select(['key', 'value'])->one();
    }

    



    protected function getCacheKey($key)
    {
        return [
            __CLASS__,
            $this->cachePrefix,
            $key
        ];
    }

    public function tablesExists(): bool
    {
        return \Yii::$app->db->getTableSchema($this->modelClass::tableName()) !== null;
    }
}
