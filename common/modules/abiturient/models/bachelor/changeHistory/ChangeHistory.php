<?php

namespace common\modules\abiturient\models\bachelor\changeHistory;

use common\models\EntrantManager;
use common\models\interfaces\IHaveIgnoredOnCopyingAttributes;
use common\models\relation_presenters\OneToManyRelationPresenter;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\rows\interfaces\IChangeHistoryRow;
use common\modules\abiturient\models\drafts\IHasRelations;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;




















class ChangeHistory extends ActiveRecord implements IHasRelations, IHaveIgnoredOnCopyingAttributes
{
    use HtmlPropsEncoder;

    




    public const CHANGE_HISTORY_TYPE_DEFAULT = 0;

    


    public const CHANGE_HISTORY_FILE = 1;

    


    public const CHANGE_HISTORY_EXAM_SET = 2;

    


    public const CHANGE_HISTORY_EXAM_POINTS = 3;

    


    public const CHANGE_HISTORY_APPLICATION_MODERATE = 4;

    


    public const CHANGE_HISTORY_APPLICATION_SENT = 5;

    


    public const CHANGE_HISTORY_APPLICATION_REJECT = 6;

    


    public const CHANGE_HISTORY_NEW_AGREEMENT = 7;

    


    public const CHANGE_HISTORY_AGREEMENT_DECLINE = 8;

    


    public const CHANGE_HISTORY_APPLICATION_RETURN_DOCS = 9;

    


    public const CHANGE_HISTORY_ABITURIENT_JUXTAPOSITION = 10;
    


    public const CHANGE_HISTORY_ENTRANCE_TEST_DATE_PASSING = 11;

    


    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    


    public static function tableName()
    {
        return '{{%change_history}}';
    }

    


    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'application_id', 'questionary_id', 'initiator_id', 'change_type'], 'integer'],
            [['application_id'], 'exist', 'skipOnError' => true, 'targetClass' => BachelorApplication::class, 'targetAttribute' => ['application_id' => 'id']],
            [['initiator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['initiator_id' => 'id']],
            [['entrant_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => EntrantManager::class, 'targetAttribute' => ['entrant_manager_id' => 'id']],
            [['questionary_id'], 'exist', 'skipOnError' => true, 'targetClass' => AbiturientQuestionary::class, 'targetAttribute' => ['questionary_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'change_type' => 'Change Type',
            'application_id' => 'Application ID',
            'questionary_id' => 'Questionary ID',
            'initiator_id' => 'Initiator ID',
        ];
    }

    



    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    




    public function getInitiator()
    {
        return $this->hasOne(User::class, ['id' => 'initiator_id']);
    }

    




    public function getQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id']);
    }

    




    public function getChangeHistoryEntityClasses()
    {
        return $this->hasMany(ChangeHistoryEntityClass::class, ['change_id' => 'id']);
    }

    public function getRow(): IChangeHistoryRow
    {
        $classString = ChangeHistoryClasses::getRowByChangeTypeId($this->change_type);
        return new $classString($this);
    }

    public function beforeDelete()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $errorFrom = '';
            $deleteSuccess = true;
            $changeHistoryEntityClasses = $this->changeHistoryEntityClasses;
            if (!empty($changeHistoryEntityClasses)) {
                foreach ($changeHistoryEntityClasses as $dataToDelete) {
                    $deleteSuccess = $dataToDelete->delete();
                    if (!$deleteSuccess) {
                        $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                        break;
                    }
                }
            }

            if ($deleteSuccess) {
                $transaction->commit();
            } else {
                Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");
                $transaction->rollBack();
            }
            return $deleteSuccess;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function getEntrantManager()
    {
        return $this->hasOne(EntrantManager::class, ['id' => 'entrant_manager_id']);
    }

    public function getRelationsInfo(): array
    {
        return [
            new OneToManyRelationPresenter('changeHistoryEntityClasses', [
                'parent_instance' => $this,
                'child_class' => ChangeHistoryEntityClass::class,
                'child_column_name' => 'change_id',
                'actual_relation_name' => 'changeHistoryEntityClasses',
                'find_exists_child' => false,
                'make_new_child' => true,
                'ignore_in_comparison' => true
            ]),
        ];
    }

    public function getIgnoredOnCopyingAttributes(): array
    {
        return [
            'id',
            'updated_at'
        ];
    }
}
