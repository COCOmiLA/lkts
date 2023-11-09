<?php

namespace common\modules\student\components\graduateWork;

use Yii;
use yii\base\Widget;

class GraduateWorkWidget extends Widget
{
    public $recordBook_id;

    public $recordBooks;
    public $themes;

    public function init() {
        parent::init();

        $graduateWorkLoader = Yii::$app->getModule('student')->graduateWorkLoader;
        $graduateWorkLoader->setParams(Yii::$app->user->identity->guid);

        $this->recordBooks = $graduateWorkLoader->loadRecordBooks();
        if ($this->recordBook_id == null && sizeof($this->recordBooks) > 0) {
            $this->recordBook_id = $this->recordBooks[0]->id;
        }

        $this->themes = $graduateWorkLoader->loadCourseGraduateWorks($this->recordBook_id);

    }

    public function run() {
        return $this->render('graduateWork_widget', [
            'recordBooks' => $this->recordBooks,
            'themes' => $this->themes,
            'recordBook_id' => $this->recordBook_id,
        ]);
    }
}