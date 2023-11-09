<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\traits;

use Yii;









trait FlashTrait
{
    




    public function alert($message, $removeAfterAccess = true)
    {
        $this->warning($message, $removeAfterAccess);
    }

    




    public function danger($message, $removeAfterAccess = true)
    {
        Yii::$app->session->addFlash('danger', $message, $removeAfterAccess);
    }

    




    public function error($message, $removeAfterAccess = true)
    {
        $this->danger($message, $removeAfterAccess);
    }

    




    public function info($message, $removeAfterAccess = true)
    {
        Yii::$app->session->addFlash('info', $message, $removeAfterAccess);
    }

    




    public function ok($message, $removeAfterAccess = true)
    {
        $this->success($message, $removeAfterAccess);
    }

    




    public function success($message, $removeAfterAccess = true)
    {
        Yii::$app->session->addFlash('success', $message, $removeAfterAccess);
    }

    




    public function warning($message, $removeAfterAccess = true)
    {
        Yii::$app->session->addFlash('warning', $message, $removeAfterAccess);
    }
}
