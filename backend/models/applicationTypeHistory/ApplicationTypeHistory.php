<?php

namespace backend\models\applicationTypeHistory;

use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
















class ApplicationTypeHistory extends ActiveRecord
{
    
    public const CHANGE_BOOLEAN_SETTINGS_ATTRIBUTE = 'change-boolean-settings-attribute';

    
    public const PUT_APPLICATION_TYPE_IN_ARCHIVE = 'put-application-type-in-archive';

    
    public const BLOCK_APPLICATION_TYPE = 'block-application-type';

    
    public const DELETE_PREPARED_APPLICATION = 'delete-prepared-application';

    
    public const UNBLOCK_APPLICATION_TYPE = 'unblock-application-type';

    
    public const UPDATE_CAMPAIGN_INFO_DICTIONARY = 'update-campaign-info-dictionary';

    


    public static function tableName()
    {
        return '{{%application_type_history}}';
    }

    


    public function behaviors()
    {
        return ['timestamp' => ['class' => TimestampBehavior::class]];
    }

    


    public function rules()
    {
        return [
            [
                ['application_type_id'],
                'required'
            ],
            [
                ['change_class'],
                'string',
                'max' => 500,
            ],
            [
                ['change_type'],
                'string',
                'max' => 50,
            ],
            [
                [
                    'application_type_id',
                    'initiator_id',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [
                ['application_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ApplicationType::class,
                'targetAttribute' => ['application_type_id' => 'id']
            ],
            [
                ['initiator_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['initiator_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getApplicationType()
    {
        return $this->hasOne(ApplicationType::class, ['id' => 'application_type_id']);
    }

    




    public function getApplicationTypeHistoryEntityClassInputs()
    {
        return $this->hasMany(ApplicationTypeHistoryEntityClassInput::class, ['application_type_history_id' => 'id']);
    }

    




    public function getInitiator()
    {
        return $this->hasOne(User::class, ['id' => 'initiator_id']);
    }

    








    public static function createNewEntry(
        User   $initiator,
        string $changeType,
        int    $applicationTypeId,
        string $className = null,
        array  $listChanges = []
    ): bool {
        $transaction = Yii::$app->db->beginTransaction();

        $history = new ApplicationTypeHistory();
        $history->initiator_id = $initiator->id;
        $history->change_type = $changeType;
        $history->change_class = $className;
        $history->application_type_id = $applicationTypeId;
        if (!$history->save()) {
            Yii::error('Ошибка сохранения истории изменений', 'createNewEntry');
            $transaction->rollBack();
            return false;
        }

        if (!$listChanges) {
            $transaction->commit();
            return true;
        }

        foreach ($listChanges as $changes) {
            $historyInput = new ApplicationTypeHistoryEntityClassInput();
            $historyInput->application_type_history_id = $history->id;
            $historyInput->input_name = (string) $changes['attr'];
            $historyInput->old_value = (string) $changes['oldValue'];
            $historyInput->actual_value = (string) $changes['newValue'];

            if (!$historyInput->save()) {
                Yii::error('Ошибка сохранения истории изменений атрибута', 'createNewEntry');
                $transaction->rollBack();
                return false;
            }
        }

        $transaction->commit();
        return true;
    }

    


    public function translateCause()
    {
        switch ($this->change_type) {
            case self::CHANGE_BOOLEAN_SETTINGS_ATTRIBUTE:
                return 'Изменены настройки ПК';

            case self::PUT_APPLICATION_TYPE_IN_ARCHIVE:
                return 'Помещение ПК в архив';

            case self::BLOCK_APPLICATION_TYPE:
                return 'Блокировка ПК';

            case self::DELETE_PREPARED_APPLICATION:
                return 'Удалил зависимые с ПК заявления в статусе "готовится"';

            case self::UNBLOCK_APPLICATION_TYPE:
                return 'Разблокировка ПК';

            case self::UPDATE_CAMPAIGN_INFO_DICTIONARY:
                return 'Обновлена информация о ПК из 1С';

            default:
                return '-';
        }
    }

    


    public function hasApplicationTypeHistoryEntityClassInputs()
    {
        return $this->getApplicationTypeHistoryEntityClassInputs()->exists();
    }
}
