<?php

namespace common\modules\abiturient\controllers;

use common\components\filesystem\FilterFilename;
use common\models\settings\ChatSettings;
use common\models\User;
use common\modules\abiturient\models\chat\ChatBase;
use common\modules\abiturient\models\chat\ChatFileBase;
use common\modules\abiturient\models\chat\ChatPersonToPerson;
use common\modules\abiturient\models\chat\ChatUserBase;
use common\modules\abiturient\models\chat\EmptyChatUser;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class BaseChatController extends Controller
{
    public function beforeAction($action)
    {
        if (!ChatSettings::getValueByName(ChatSettings::ENABLE_CHAT)) {
            throw new ForbiddenHttpException('Чат отключён администратором');
        }
        return parent::beforeAction($action);
    }

    public function init()
    {
        parent::init();

        $path = realpath(__DIR__);
        $path = FileHelper::normalizePath("{$path}/../views/chat");
        Yii::setAlias('@chatView', $path);

        $path = realpath(__DIR__);
        $path = FileHelper::normalizePath("{$path}/../views/chat/_partials");
        Yii::setAlias('@chatPartialView', $path);
    }

    public static function otherUsersClass(): string
    {
        return ChatUserBase::class;
    }

    public function getViewPath()
    {
        return Yii::getAlias('@chatView');
    }

    


    public function renderActionIndex(string $viewPath = 'index')
    {
        $data = $this->processDataActionIndex();

        return $this->render(
            $viewPath,
            $data
        );
    }

    


    public function processDataActionIndex(): array
    {
        $user = Yii::$app->user->identity;

        $otherUsersClass = static::otherUsersClass();
        $availableUsersWithChats = $otherUsersClass::getAvailableUsersWithChats($user);
        $availableUsersWithoutChats = $otherUsersClass::getAvailableUsersWithoutChats($user);

        return compact([
            'user',
            'availableUsersWithoutChats',
            'availableUsersWithChats',
        ]);
    }

    public function actionOpenChat()
    {
        Yii::$app->response->statusCode = 200;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $chat = null;
        $history = '';
        $destinationUser = null;
        if (Yii::$app->request->isAjax) {
            $thisUser = Yii::$app->user->identity;
            $thatUserId = '';
            $destinationId = Yii::$app->request->post('destinationId');
            if ($destinationId === ChatBase::ID_FOR_NEW_CHAT || $destinationId === ChatBase::ID_FOR_ARCHIVE_CHAT) {
                $thatUserId = ChatBase::ID_FOR_NEW_CHAT;
            } else {
                $thatUser = User::findOne((int)$destinationId);
                $thatUserId = $thatUser->id;
            }

            $chat = ChatPersonToPerson::findChatByIdAndUsersId(
                (int)Yii::$app->request->post('chatId'),
                [$thisUser->id, $thatUserId]
            );

            $destinationUser = null;
            if ($destinationId !== ChatBase::ID_FOR_NEW_CHAT) {
                if (!$thatUser) {
                    throw new ServerErrorHttpException('Не удалось определить пользователя чата');
                }
                $otherUsersClass = static::otherUsersClass();
                $destinationUser = $otherUsersClass::getOrCreateUser($chat, $thatUser);
            } else {
                $destinationUser = EmptyChatUser::getOrCreateUser($chat, $thisUser);
            }

            if ($chat) {
                $history = $chat->renderHistory($this, $thisUser);
            }
        }

        return [
            'history' => $history,
            'chatId' => $chat ? $chat->id : null,
            'header' => $destinationUser ? $destinationUser->renderHeader($this) : []
        ];
    }

    public function actionSendMessage()
    {
        $chatId = null;
        $blobsUid = [];
        Yii::$app->response->statusCode = 200;
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $hasError = false;
            $user = Yii::$app->user->identity;

            $fileContexts = json_decode(Yii::$app->request->post('fileContexts'), true);

            $messageOutput = '';
            $messageContext = json_decode(Yii::$app->request->post('messageContext'), true);
            if ($messageContext) {
                $messageOutput = $messageContext['messageOutput'];
            }

            $blobsUid = $this->buildBlobsUid($messageContext, $fileContexts);

            $chat = null;
            try {
                $chat = ChatPersonToPerson::getOrCreateChat(
                    (int)Yii::$app->request->post('chatId'),
                    json_decode(Yii::$app->request->post('usersIds'))
                );
            } catch (Throwable $th) {
                Yii::error("Ошибка содания чата при отправке сообщения: {$th->getMessage()}", 'BaseChatController.actionSendMessage');
                Yii::$app->response->statusCode = 400;
                $hasError = true;
            }

            if (!$hasError && $chat) {
                $chatId = $chat->id;
            } else {
                Yii::$app->response->statusCode = 400;
                $hasError = true;
            }

            if (!$hasError && $messageContext) {
                try {
                    if (!$chat->addMessage($messageOutput, $user->id)) {
                        Yii::$app->response->statusCode = 400;
                        $hasError = true;
                    }
                } catch (Throwable $th) {
                    Yii::error("Ошибка отправки сообщения: {$th->getMessage()}", 'BaseChatController.actionSendMessage');
                    Yii::$app->response->statusCode = 400;
                    $hasError = true;
                }
            }

            if (!$hasError && $fileContexts) {
                try {
                    if (!$chat->addFiles($fileContexts, $user->id)) {
                        Yii::$app->response->statusCode = 400;
                    }
                } catch (Throwable $th) {
                    Yii::error("Ошибка отправки файла: {$th->getMessage()}", 'BaseChatController.actionSendMessage');
                    Yii::$app->response->statusCode = 400;
                }
            }
        }

        return [
            'chatId' => $chatId,
            'blobsUid' => $blobsUid,
        ];
    }

    





    private function buildBlobsUid(array $messageContext, array $fileContexts): array
    {
        $blobsUid = [];
        if ($messageContext) {
            $tmp = ArrayHelper::getValue($messageContext, 'messageUid');
            if ($tmp) {
                $blobsUid[] = $tmp;
            }
        }
        if ($fileContexts) {
            foreach ($fileContexts as $context) {
                $tmp = ArrayHelper::getValue($context, 'fileUid');
                if ($tmp) {
                    $blobsUid[] = $tmp;
                }
            }
        }

        return $blobsUid;
    }

    


    public function renderActionUpdateChatPeopleList(string $viewPath = '@chatPartialView/chat-people-list')
    {
        if (Yii::$app->request->isAjax) {
            $data = $this->processDataActionIndex();

            return $this->renderPartial(
                $viewPath,
                $data
            );
        }

        return '';
    }

    public function actionDownload(int $id = null, int $key = null)
    {
        if (is_null($id) && !is_null($key)) {
            
            $id = $key;
        }

        if (is_null($id)) {
            throw new UserException('Невозможно скачать файл, так как не передан уникальный идентификатор файла.');
        }

        $file = ChatFileBase::findOne($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($file != null) {
            $user = Yii::$app->user->identity;
            if ($file->checkAccess($user)) {
                $abs_path = $file->getAbsPath();
                if ($abs_path && file_exists($abs_path)) {
                    return Yii::$app->response->sendFile(
                        $abs_path,
                        FilterFilename::sanitize($file->filename),
                        [
                            'mimeType' => $file->getMimeType(),
                            'inline' => $file->extension === 'pdf'
                        ]
                    );
                }
                return Yii::t(
                    'abiturient/chat/chat-file',
                    'Текст сообщения об отсутствии файла для файла в чате: `Невозможно получить файл.`'
                );
            }
            return Yii::t(
                'abiturient/chat/chat-file',
                'Текст сообщения об отсутствии доступа к записи для файла в чате: `У вас нет доступа для скачивания этого файла.`'
            );
        }
        return Yii::t(
            'abiturient/chat/chat-file',
            'Текст сообщения об записи об таком файле для файла в чате: `Файл не найден.`'
        );
    }
}
