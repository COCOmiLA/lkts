<?php

namespace common\modules\student\components\stipend;

use Yii;
use yii\base\Widget;

class StipendWidget extends Widget
{

    public $recordBook_id;

    public $recordBooks;
    public $stipends;

    public function init() {
        parent::init();

        $stipendLoader = Yii::$app->getModule('student')->stipendLoader;
        $stipendLoader->setParams(Yii::$app->user->identity->guid);

        $this->recordBooks = $stipendLoader->loadRecordBooks();
        if ($this->recordBook_id == null && sizeof($this->recordBooks) > 0) {
            $this->recordBook_id = $this->recordBooks[0]->id;
        }

        $this->stipends = $stipendLoader->loadStudentStipends($this->recordBook_id);

    }

    public function run() {
        return $this->render('stipend_widget', [
            'recordBooks' => $this->recordBooks,
            'stipends' => $this->stipends,
            'recordBook_id' => $this->recordBook_id,
        ]);
    }
}