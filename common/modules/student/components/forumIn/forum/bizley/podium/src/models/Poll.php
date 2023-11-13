<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\Query;
use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\PollActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Exception;
use yii\helpers\ArrayHelper;












class Poll extends PollActiveRecord
{
    



    public function getSortedAnswers()
    {
        return $this->getAnswers()->orderBy(['votes' => SORT_DESC])->all();
    }

    




    public function getUserVoted($userId)
    {
        return (new Query())->from('{{%podium_poll_vote}}')->where([
            'poll_id' => $this->id,
            'caster_id' => $userId
        ])->count('id') ? true : false;
    }

    





    public function vote($userId, $answers)
    {
        $votes = [];
        $time = time();
        foreach ($answers as $answer) {
            $votes[] = [$this->id, $answer, $userId, $time];
        }
        if (!empty($votes)) {
            $transaction = static::getDb()->beginTransaction();
            try {
                if (!Podium::getInstance()->db->createCommand()->batchInsert(
                        '{{%podium_poll_vote}}', ['poll_id', 'answer_id', 'caster_id', 'created_at'], $votes
                    )->execute()) {
                    throw new Exception('Votes saving error!');
                }
                if (!PollAnswer::updateAllCounters(['votes' => 1], ['id' => $answers])) {
                    throw new Exception('Votes adding error!');
                }
                $transaction->commit();
                return true;
            } catch (Exception $e) {
                $transaction->rollBack();
                Log::error($e->getMessage(), $this->id, __METHOD__);
            }
        }
        return false;
    }

    




    public function hasAnswer($answerId)
    {
        foreach ($this->answers as $answer) {
            if ($answer->id == $answerId) {
                return true;
            }
        }
        return false;
    }

    



    public function getCurrentVotes()
    {
        $this->refresh();
        return ArrayHelper::map($this->answers, 'id', 'votes');
    }

    



    public function getVotesCount()
    {
        $votes = 0;
        foreach ($this->answers as $answer) {
            $votes += $answer->votes;
        }
        return $votes;
    }

    



    public function podiumDelete()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            if (!Podium::getInstance()->db->createCommand()->delete('{{%podium_poll_vote}}', ['poll_id' => $this->id])->execute()) {
                throw new Exception('Poll Votes deleting error!');
            }
            if (!PollAnswer::deleteAll(['poll_id' => $this->id])) {
                throw new Exception('Poll Answers deleting error!');
            }
            if (!$this->delete()) {
                throw new Exception('Poll deleting error!');
            }
            $transaction->commit();
            Log::info('Poll deleted', !empty($this->id) ? $this->id : '', __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    



    public function podiumEdit()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            if (!$this->save()) {
                throw new Exception('Poll saving error!');
            }

            foreach ($this->editAnswers as $answer) {
                foreach ($this->answers as $oldAnswer) {
                    if ($answer == $oldAnswer->answer) {
                        continue(2);
                    }
                }
                $pollAnswer = new PollAnswer();
                $pollAnswer->poll_id = $this->id;
                $pollAnswer->answer = $answer;
                if (!$pollAnswer->save()) {
                    throw new Exception('Poll Answer saving error!');
                }
            }
            foreach ($this->answers as $oldAnswer) {
                foreach ($this->editAnswers as $answer) {
                    if ($answer == $oldAnswer->answer) {
                        continue(2);
                    }
                }
                if (!$oldAnswer->delete()) {
                    throw new Exception('Poll Answer deleting error!');
                }
            }

            $transaction->commit();
            Log::info('Poll updated', $this->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
