<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\poll;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Poll as PollModel;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Thread;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use yii\base\Widget;
use yii\bootstrap\ActiveForm;








class Poll extends Widget
{
    


    public $model;

    


    public $display = false;


    



    public function run()
    {
        if (!$this->model) {
            return null;
        }
        $hidden = $this->model->hidden;
        if ($hidden && !empty($this->model->end_at) && $this->model->end_at < time()) {
            $hidden = 0;
        }
        return $this->render('view', [
            'model' => $this->model,
            'hidden' => $hidden,
            'voted' => $this->display ? true : $this->model->getUserVoted(User::loggedId()),
            'display' => $this->display
        ]);
    }

    





    public static function create($form, $model)
    {
        return (new static)->render('create', ['form' => $form, 'model' => $model]);
    }

    





    public static function update($form, $model)
    {
        return (new static)->render('update', ['form' => $form, 'model' => $model]);
    }

    




    public static function preview($model)
    {
        if (!$model->pollAdded) {
            return null;
        }
        return (new static)->render('preview', ['model' => $model]);
    }
}
