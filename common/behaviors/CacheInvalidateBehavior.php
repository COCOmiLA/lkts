<?php




namespace common\behaviors;

use Yii;
use yii\base\Behavior;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;


























class CacheInvalidateBehavior extends Behavior
{
    


    public $cacheComponent = 'cache';
    


    public $tags = [];
    


    public $keys = [];

    


    private $cache;


    



    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'invalidateCache',
            ActiveRecord::EVENT_AFTER_INSERT => 'invalidateCache',
            ActiveRecord::EVENT_AFTER_UPDATE => 'invalidateCache',
        ];
    }

    



    public function invalidateCache()
    {
        if (!empty($this->keys)) {
            $this->invalidateKeys();
        }
        if (!empty($this->tags)) {
            $this->invalidateTags();
        }
        return true;
    }

    


    protected function invalidateKeys()
    {
        foreach ($this->keys as $key) {
            if (is_callable($key)) {
                $key = call_user_func($key, $this->owner);
            }
            $this->getCache()->delete($key);
        }
    }

    


    protected function invalidateTags()
    {
        TagDependency::invalidate(
            $this->getCache(),
            array_map(function ($tag) {
                if (is_callable($tag)) {
                    $tag = call_user_func($tag, $this->owner);
                }
                return $tag;
            }, $this->tags)
        );
    }

    


    protected function getCache()
    {
        return $this->cache ?: Yii::$app->{$this->cacheComponent};
    }
}
