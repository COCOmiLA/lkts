<?php

namespace common\services\abiturientController\bachelor;

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\CommentsComing;
use common\services\abiturientController\bachelor\BachelorService;
use Yii;

class CommentService extends BachelorService
{
    




    public function commentPostProcessing(
        CommentsComing $model,
        BachelorApplication $application,
        int $idUser
    ): array {
        $comSaved = null;
        $alertBody = null;
        $alertClass = null;
        $comNotSaved = null;

        if ($model->load($this->request->post())) {
            $model->author_id = $idUser;
            $model->bachelor_application_id = $application->id;

            if ($model->validate()) {
                if ($model->hasChangedAttributes()) {
                    if ($model->save(false)) {
                        $application->resetStatus();

                        $comSaved = Yii::t(
                            'abiturient/bachelor/comment/all',
                            'Текст сообщения об успешном сохранении комментария; на страницы комментария: `Комментарий сохранен`'
                        );
                    } else {
                        $comNotSaved = Yii::t(
                            'abiturient/bachelor/comment/all',
                            'Текст сообщения о не удачном сохранении комментария; на страницы комментария: `Ошибка сохранения`'
                        );
                    }
                } else {
                    $alertBody = $this->configurationManager->getText('no_data_saved_text', $application->type ?? null);
                    $alertClass = 'alert-warning';
                }
            } else {
                $comNotSaved = Yii::t(
                    'abiturient/bachelor/comment/all',
                    'Текст сообщения о не удачной валидации формы комментария; на страницы комментария: `Ошибка валидации`'
                );
            }
        }

        return [
            'comSaved' => $comSaved,
            'alertBody' => $alertBody,
            'alertClass' => $alertClass,
            'comNotSaved' => $comNotSaved,
        ];
    }
}
