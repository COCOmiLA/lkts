<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\base\View;
use yii\caching\Cache;
use yii\di\Instance;
use yii\widgets\FragmentCache;






























class PodiumCache extends BaseObject
{
    



    protected $_cachePrefix = 'podium.';

    





    public function getEngine()
    {
        return Instance::ensure(Podium::getInstance()->cache, Cache::class);
    }

    












    public function begin($key, $view, $duration = 60)
    {
        $properties['id'] = $this->_cachePrefix . $key;
        $properties['view'] = $view;
        $properties['duration'] = $duration;

        $cache = FragmentCache::begin($properties);
        if ($cache->getCachedContent() !== false) {
            $this->end();
            return false;
        }
        return true;
    }

    




    public static function clearAfter($what)
    {
        $cache = new static;

        switch ($what) {
            case 'userDelete':
                $cache->delete('forum.latestposts');
                
            case 'activate':
                $cache->delete('members.fieldlist');
                $cache->delete('forum.memberscount');
                break;
            case 'categoryDelete':
            case 'forumDelete':
            case 'threadDelete':
            case 'postDelete':
                $cache->delete('forum.threadscount');
                $cache->delete('forum.postscount');
                $cache->delete('user.threadscount');
                $cache->delete('user.postscount');
                $cache->delete('forum.latestposts');
                break;
            case 'threadMove':
            case 'postMove':
                $cache->delete('forum.threadscount');
                $cache->delete('forum.postscount');
                $cache->delete('forum.latestposts');
                break;
            case 'newThread':
                $cache->delete('forum.threadscount');
                $cache->deleteElement('user.threadscount', User::loggedId());
                
            case 'newPost':
                $cache->delete('forum.postscount');
                $cache->delete('forum.latestposts');
                $cache->deleteElement('user.postscount', User::loggedId());
                break;
        }
    }

    




    public function delete($key)
    {
        return $this->engine->delete($this->_cachePrefix . $key);
    }

    





    public function deleteElement($key, $element)
    {
        $cache = $this->get($key);
        if ($cache !== false && isset($cache[$element])) {
            unset($cache[$element]);
            return $this->set($key, $cache);
        }
        return true;
    }

    


    public function end()
    {
        return FragmentCache::end();
    }

    


    public function flush()
    {
        return $this->engine->flush();
    }

    





    public function get($key)
    {
        return $this->engine->get($this->_cachePrefix . $key);
    }

    






    public function getElement($key, $element)
    {
        $cache = $this->get($key);
        if ($cache !== false && isset($cache[$element])) {
            return $cache[$element];
        }
        return false;
    }

    






    public function set($key, $value, $duration = 0)
    {
        return $this->engine->set($this->_cachePrefix . $key, $value, $duration);
    }

    







    public function setElement($key, $element, $value, $duration = 0)
    {
        $cache = $this->get($key);
        if ($cache === false) {
            $cache = [];
        }
        $cache[$element] = $value;
        return $this->set($key, $cache, $duration);
    }
}
