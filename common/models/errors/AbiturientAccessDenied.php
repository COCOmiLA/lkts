<?php

namespace common\models\errors;

use common\models\User;
use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

class AbiturientAccessDenied extends NotFoundHttpException
{
    



    public function __construct(Model $model, ?User $user = null)
    {
        if (!$user) {
            AbiturientAccessDenied::userNotFound($model);
        } else {
            AbiturientAccessDenied::accessDenied($model, $user);
        }

        $exceptionMessage = Yii::t(
            'errors/abiturient-access-denied',
            'Вы не имеете доступ для просмотра данной страницы'
        );
        parent::__construct($exceptionMessage);
    }

    




    private function userNotFound(Model $model): void
    {
        $formName = $model->formName();

        Yii::error("Для модели «{$formName}» (ID = {$model->id}) не найден поступающий.", 'AbiturientAccessDenied.userNotFound');
    }

    





    public function accessDenied(Model $model, User $user): void
    {
        $formName = $model->formName();

        Yii::error("Для модели «{$formName}» (ID = {$model->id}), поступающий «{$user->email}» (ID = {$user->id}) не является автором записи.", 'AbiturientAccessDenied.accessDenied');
    }
}
