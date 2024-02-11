<?php

namespace common\modules\student\components\session;

use common\models\EmptyCheck;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class SessionWidget extends Widget
{
    public $grades;
    public $user_guid;

    public function init()
    {
        parent::init();
        $user_guid = Yii::$app->request->get('user_guid');
        if (EmptyCheck::isEmpty($user_guid)) {
            $user_guid = ArrayHelper::getValue(Yii::$app->user->identity, 'userRef.reference_id');
        }

        
        $gradeLoader = Yii::$app->getModule('student')->gradeLoader;
        $gradeLoader->setParams($user_guid);
        $this->user_guid = $user_guid;
        $this->grades = $gradeLoader->loadGrades();
    }

    public function run()
    {
        if (!empty($this->grades)) {
            return $this->render('grade_widget', ['grades' => $this->grades, 'user_guid' => $this->user_guid]);
        }

        return implode("\n", [
            Html::beginTag('h4'),
            'Оценки не найдены',
            Html::endTag('h4')
        ]) . "\n";
    }
}
