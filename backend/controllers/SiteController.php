<?php

namespace backend\controllers;

use backend\models\MainPageInstructionFile;
use backend\models\MainPageSetting;
use common\components\filesystem\FilterFilename;
use common\components\keyStorage\FormModel;
use common\models\AttachmentTypeTemplate;
use Yii;
use yii\base\UserException;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;




class SiteController extends Controller
{
    


    public function actions()
    {
        return ['error' => ['class' => ErrorAction::class]];
    }

    public function beforeAction($action)
    {
        $this->layout = Yii::$app->user->isGuest || !Yii::$app->user->can('loginToBackend') ? 'base' : 'common';
        return parent::beforeAction($action);
    }

    public function actionSettings()
    {
        $model = new FormModel([
            'keys' => [
                'frontend.maintenance' => [
                    'label' => Yii::t('backend', 'Сервисный режим фронтенд части'),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        'disabled' => Yii::t('backend', 'Неактивно'),
                        'enabled' => Yii::t('backend', 'Активно')
                    ]
                ],
                'backend.theme-skin' => [
                    'label' => Yii::t('backend', 'Тема панели управления'),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        'navbar-dark bg-gray' => Yii::t('backend', 'Серый'),
                        'navbar-dark bg-dark' => Yii::t('backend', 'Темный'),
                        'navbar-light bg-white' => Yii::t('backend', 'Белый'),
                        'navbar-light bg-pink' => Yii::t('backend', 'Розовый'),
                        'navbar-dark bg-indigo' => Yii::t('backend', 'Индиго'),
                        'navbar-light bg-light' => Yii::t('backend', 'Светлый'),
                        'navbar-dark bg-primary' => Yii::t('backend', 'Синий'),
                        'navbar-dark bg-danger' => Yii::t('backend', 'Красный'),
                        'navbar-light bg-info' => Yii::t('backend', 'Берёзовый'),
                        'navbar-light bg-warning' => Yii::t('backend', 'Жёлтый'),
                        'navbar-light bg-success' => Yii::t('backend', 'Зелёный'),
                        'navbar-dark bg-navy' => Yii::t('backend', 'Тёмно-синий'),
                        'navbar-light bg-orange' => Yii::t('backend', 'Оранжевый'),
                        'navbar-dark bg-purple' => Yii::t('backend', 'Пурпурный'),
                        'navbar-dark bg-lightblue' => Yii::t('backend', 'Светло-синий'),
                        'navbar-light bg-teal' => Yii::t('backend', 'Цвет морской волны'),
                    ],
                ],
                'backend.logo-skin' => [
                    'label' => Yii::t('backend', 'Тема логотипа'),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        'bg-gray' => Yii::t('backend', 'Серый'),
                        'bg-dark' => Yii::t('backend', 'Темный'),
                        'bg-white' => Yii::t('backend', 'Белый'),
                        'bg-pink' => Yii::t('backend', 'Розовый'),
                        'bg-indigo' => Yii::t('backend', 'Индиго'),
                        'bg-light' => Yii::t('backend', 'Светлый'),
                        'bg-primary' => Yii::t('backend', 'Синий'),
                        'bg-danger' => Yii::t('backend', 'Красный'),
                        'bg-info' => Yii::t('backend', 'Берёзовый'),
                        'bg-warning' => Yii::t('backend', 'Жёлтый'),
                        'bg-success' => Yii::t('backend', 'Зелёный'),
                        'bg-navy' => Yii::t('backend', 'Тёмно-синий'),
                        'bg-orange' => Yii::t('backend', 'Оранжевый'),
                        'bg-purple' => Yii::t('backend', 'Пурпурный'),
                        'bg-lightblue' => Yii::t('backend', 'Светло-синий'),
                        'bg-teal' => Yii::t('backend', 'Цвет морской волны'),
                    ],
                ],
                'backend.nav-style' => [
                    'label' => Yii::t('backend', 'Стиль панели управления'),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        '' => Yii::t('backend', 'По умолчанию'),
                        'nav-flat' => Yii::t('backend', 'Flat'),
                        'nav-legacy' => Yii::t('backend', 'Legacy'),
                    ],
                ],
                'backend.layout-fixed' => [
                    'label' => Yii::t('backend', 'Фиксированная панель управления'),
                    'type' => FormModel::TYPE_CHECKBOX
                ],
                'backend.nav-compact' => [
                    'label' => Yii::t('backend', 'Компактное представление панели управления'),
                    'type' => FormModel::TYPE_CHECKBOX
                ],
                'backend.small-body-text' => [
                    'label' => Yii::t('backend', 'Использовать маленький текст'),
                    'type' => FormModel::TYPE_CHECKBOX
                ],
                'backend.nav-child-indent' => [
                    'label' => Yii::t('backend', 'Добавить отступ в сворачиваемые списки боковой панели'),
                    'type' => FormModel::TYPE_CHECKBOX
                ],
                'backend.dark-mode' => [
                    'label' => Yii::t('backend', 'Темный режим'),
                    'type' => FormModel::TYPE_CHECKBOX
                ],
                'backend.layout-collapsed-sidebar' => [
                    'label' => Yii::t('backend', 'Скрыть боковую панель'),
                    'type' => FormModel::TYPE_CHECKBOX
                ]
            ]
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Настройки были успешно сохранены'),
                'options' => ['class' => 'alert alert-success']
            ]);
            return $this->refresh();
        }

        return $this->render('settings', ['model' => $model]);
    }

    public function actionError()
    {
        $error = Yii::$app->errorHandler->error;
        if ($error) {
            return $this->render('error', ['error' => $error]);
        }
    }

    public function actionDownloadInstructionAttachment(int $id)
    {
        if (is_null($id)) {
            throw new UserException('Невозможно скачать файл, так как не передан уникальный идентификатор файла.');
        }

        $setting = MainPageSetting::findOne($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (
            $setting &&
            $instruction = MainPageSetting::getRelatedInstruction($setting)
        ) {
            if (!$instruction instanceof MainPageInstructionFile) {
                return Yii::t(
                    'abiturient/download-instruction-attachment',
                    'Текст сообщения об отсутствии файла для инструкции поступающего: `Невозможно получить информацию о файле.`'
                );
            }

            $path = $instruction->getAbsPath();
            if (!$path || !file_exists($path)) {
                return Yii::t(
                    'abiturient/download-instruction-attachment',
                    'Текст сообщения об отсутствии файла для инструкции поступающего: `Невозможно получить файл.`'
                );
            }

            return Yii::$app->response->sendFile(
                $path,
                FilterFilename::sanitize($instruction->filename)
            );
        }

        return Yii::t(
            'abiturient/download-instruction-attachment',
            'Текст сообщения об отсутствии записи о таком файле для инструкции поступающего: `Файл не найден.`'
        );
    }

    public function actionDownloadAttachmentTypeTemplate(int $id)
    {
        if (is_null($id)) {
            throw new UserException('Невозможно скачать файл, так как не передан уникальный идентификатор файла.');
        }

        $attachmentTypeTemplate = AttachmentTypeTemplate::findOne($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($attachmentTypeTemplate) {
            $path = $attachmentTypeTemplate->getAbsPath();
            if (!$path || !file_exists($path)) {
                return Yii::t(
                    'abiturient/download-attachment-type-template',
                    'Текст сообщения об отсутствии файла для инструкции поступающего: `Невозможно получить файл.`'
                );
            }
            $mimeType = 'application/image';
            if (strpos($path, '.pdf') !== false) {
                $mimeType = 'application/pdf';
            }

            return Yii::$app->response->sendFile(
                $path,
                FilterFilename::sanitize($attachmentTypeTemplate->filename),
                ['inline' => true, 'mimeType' => $mimeType]
            );
        }

        return Yii::t(
            'abiturient/download-attachment-type-template',
            'Текст сообщения об отсутствии записи о таком файле для инструкции поступающего: `Файл не найден.`'
        );
    }

    public function actionDeleteAttachmentTypeTemplate(int $id)
    {
        if (is_null($id)) {
            throw new UserException('Невозможно скачать файл, так как не передан уникальный идентификатор файла.');
        }

        $attachmentTypeTemplate = AttachmentTypeTemplate::findOne($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($attachmentTypeTemplate && $attachmentTypeTemplate->delete() !== false) {
            return Yii::t(
                'abiturient/download-attachment-type-template',
                'Текст сообщения об отсутствии записи о таком файле для инструкции поступающего: `Файл успешно удалён.`'
            );
        }

        return Yii::t(
            'abiturient/download-attachment-type-template',
            'Текст сообщения об отсутствии записи о таком файле для инструкции поступающего: `Файл возникла ошибка удаления файла.`'
        );
    }
}
