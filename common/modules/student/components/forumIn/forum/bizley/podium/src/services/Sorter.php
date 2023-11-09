<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\services;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\db\Query;
use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Exception;
use yii\base\Component;







class Sorter extends Component
{
    


    public $target;

    


    public $query;

    


    public $order;

    



    public function run()
    {
        try {
            $next = 0;
            $newSort = -1;
            foreach ($this->query->each() as $id => $model) {
                if ($next == $this->order) {
                    $newSort = $next++;
                }
                Podium::getInstance()->db->createCommand()->update(
                        call_user_func([$this->target, 'tableName']), ['sort' => $next], ['id' => $id]
                    )->execute();
                $next++;
            }
            if ($newSort == -1) {
                $newSort = $next;
            }
            $this->target->sort = $newSort;
            if (!$this->target->save()) {
                throw new Exception('Order saving error');
            }
            Log::info('Orded updated', $this->target->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
