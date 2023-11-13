<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\models\User as MainAppUser;
use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\ActivityActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;








class Activity extends ActivityActiveRecord
{
    






    protected static function addGuest($ip, $url)
    {
        $activity = static::find()->where(['ip' => $ip, 'user_id' => null])->limit(1)->one();
        if (empty($activity)) {
            $activity = new static;
            $activity->ip = $ip;
        }
        $activity->url = $url;
        return $activity->save();
    }

    






    protected static function addUser($ip, $url)
    {
        if (!$user = User::findMe()) {
            return false;
        }

        $activity = static::find()->where(['user_id' => $user->id])->limit(1)->one();
        if (!$activity) {
            $activity = new static;
            $activity->user_id = $user->id;
        }
        $activity->username = $user->podiumName;
        $activity->user_role = $user->role;
        $activity->user_slug = $user->podiumSlug;
        $activity->url = $url;
        $activity->ip = $ip;
        $activity->anonymous = !empty($user->meta) ? $user->meta->anonymous : 0;

        return $activity->save();
    }

    



    public static function add()
    {
        try {
            $ip = Yii::$app->request->userIp;
            $url = Yii::$app->request->url;
            if (empty($ip)) {
                $ip = '0.0.0.0';
            }
            if (Podium::getInstance()->user->isGuest) {
                if (static::addGuest($ip, $url)) {
                    return true;
                }
            } else {
                if (static::addUser($ip, $url)) {
                    return true;
                }
            }
            Log::error('Cannot log user activity', null, __METHOD__);
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    



    public static function deleteUser($id)
    {
        $activity = static::find()->where(['user_id' => $id])->limit(1)->one();
        if (empty($activity) || !$activity->delete()) {
            Log::error('Cannot delete user activity', $id, __METHOD__);
            return;
        }
        Podium::getInstance()->podiumCache->delete('forum.lastactive');
    }

    





    public static function updateName($id, $username, $slug)
    {
        $activity = static::find()->where(['user_id' => $id])->limit(1)->one();
        if (empty($activity)) {
            Log::error('Cannot update user activity', $id, __METHOD__);
            return;
        }
        $activity->username = $username;
        $activity->user_slug = $slug;
        if (!$activity->save()) {
            Log::error('Cannot update user activity', $id, __METHOD__);
            return;
        }
        Podium::getInstance()->podiumCache->delete('forum.lastactive');
    }

    




    public static function updateRole($id, $role)
    {
        $activity = static::find()->where(['user_id' => $id])->limit(1)->one();
        if (empty($activity)) {
            Log::error('Cannot update user activity', $id, __METHOD__);
            return;
        }
        $activity->user_role = $role;
        if (!$activity->save()) {
            Log::error('Cannot update user activity', $id, __METHOD__);
            return;
        }
        Podium::getInstance()->podiumCache->delete('forum.lastactive');
    }

    



    public static function lastActive()
    {
        $last = Podium::getInstance()->podiumCache->get('forum.lastactive');
        if ($last === false) {
            $time = time() - 15 * 60;
            $last = [
                'count' => static::find()->where(['>', 'updated_at', $time])->count(),
                'members' => static::find()->where(['and',
                        ['>', 'updated_at', $time],
                        ['is not', 'user_id', null],
                        ['anonymous' => 0]
                    ])->count(),
                'anonymous' => static::find()->where(['and',
                        ['>', 'updated_at', $time],
                        ['is not', 'user_id', null],
                        ['anonymous' => 1]
                    ])->count(),
                'guests' => static::find()->where(['and',
                        ['>', 'updated_at', $time],
                        ['user_id' => null]
                    ])->count(),
                'names' => [],
            ];
            $members = static::find()->where(['and',
                    ['>', 'updated_at', $time],
                    ['is not', 'user_id', null],
                    ['anonymous' => 0]
                ]);

            foreach ($members->each() as $member) {
                $user = (new User())->find()->where(['id' => $member->user_id])->one();
                if ($user->inherited_id != 0)
                    $username = (new MainAppUser())->find()->where(['id' => $user->inherited_id])->one()->username;
                else
                    $username = $member->username;
                $last['names'][$member->user_id] = [
                    'name' => $username,
                    'role' => $member->user_role,
                    'slug' => $member->user_slug,
                ];
            }
            Podium::getInstance()->podiumCache->set('forum.lastactive', $last, 60);
        }
        return $last;
    }

    



    public static function totalMembers()
    {
        $members = Podium::getInstance()->podiumCache->get('forum.memberscount');
        if ($members === false) {
            $members = User::find()->where(['!=', 'status', User::STATUS_REGISTERED])->count();
            Podium::getInstance()->podiumCache->set('forum.memberscount', $members);
        }
        return $members;
    }

    



    public static function totalPosts()
    {
        $posts = Podium::getInstance()->podiumCache->get('forum.postscount');
        if ($posts === false) {
            $posts = Post::find()->count();
            Podium::getInstance()->podiumCache->set('forum.postscount', $posts);
        }
        return $posts;
    }

    



    public static function totalThreads()
    {
        $threads = Podium::getInstance()->podiumCache->get('forum.threadscount');
        if ($threads === false) {
            $threads = Thread::find()->count();
            Podium::getInstance()->podiumCache->set('forum.threadscount', $threads);
        }
        return $threads;
    }
}
