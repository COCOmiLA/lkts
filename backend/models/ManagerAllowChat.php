<?php

namespace backend\models;

use common\models\errors\RecordNotValid;
use common\models\settings\ChatSettings;
use common\models\User;
use common\modules\abiturient\models\chat\ManagerChatUser;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;













class ManagerAllowChat extends ActiveRecord
{
    
    public $allowChat = true;

    


    public static function tableName()
    {
        return '{{%manager_allow_chat}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'nickname',
                    'manager_id',
                ],
                'required'
            ],
            [
                'allowChat',
                'boolean'
            ],
            [
                [
                    'created_at',
                    'manager_id',
                    'updated_at',
                ],
                'integer'
            ],
            [
                'nickname',
                'string'
            ],
            [
                ['manager_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['manager_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'nickname' => Yii::t('backend', 'Ник менеджера в чате'),
            'allowChat' => Yii::t('backend', 'Разрешить работу с чатом'),
        ];
    }

    




    public function getManager(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    







    public static function getOrCreate($managerId): ManagerAllowChat
    {
        $managerAllowChat = ManagerAllowChat::findOne(['manager_id' => $managerId]);
        if (!$managerAllowChat) {
            $managerAllowChat = new ManagerAllowChat();
            $managerAllowChat->allowChat = false;
            $managerAllowChat->manager_id = $managerId;
        }

        return $managerAllowChat;
    }

    




    public static function isAllowChat(): bool
    {
        if (!ChatSettings::getValueByName(ChatSettings::ENABLE_CHAT)) {
            return false;
        }

        $managerAllowChat = ManagerAllowChat::getOrCreate(Yii::$app->user->id);
        return $managerAllowChat->allowChat;
    }

    






    public static function generateTemporaryNick(User $user): string
    {
        $nick = $user->username;
        if (strpos($nick, '@') !== false) {
            $nick = ArrayHelper::getValue(explode('@', $nick), 0);
        }

        return $nick;
    }

    






    public static function loadFromPost(int $managerId): void
    {
        $managerAllowChat = ManagerAllowChat::getOrCreate($managerId);
        $oldNick = $managerAllowChat->nickname;
        $allowChat = $managerAllowChat->allowChat;
        if ($managerAllowChat->load(Yii::$app->request->post())) {
            $managerAllowChat->allowChat = boolval($managerAllowChat->allowChat);

            if (
                $oldNick != $managerAllowChat->nickname ||
                $allowChat != $managerAllowChat->allowChat
            ) {
                if ($managerAllowChat->allowChat) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if (!$managerAllowChat->save()) {
                            throw new RecordNotValid($managerAllowChat);
                        }

                        ManagerChatUser::updateUserAccount($managerId, $managerAllowChat->nickname);

                        $transaction->commit();
                    } catch (Throwable $th) {
                        Yii::error(
                            "Ошибка во обработки разрешения модератору на работу с чатом: {$th->getMessage()}",
                            'ManagerAllowChat.loadFromPost'
                        );

                        $transaction->rollBack();
                    }
                } else {
                    $managerAllowChat->delete();
                }
            }
        }
    }
}
