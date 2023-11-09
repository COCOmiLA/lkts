<?php

namespace common\modules\abiturient\models\bachelor\changeHistory;


use common\components\changeHistoryHandler\valueGetterHandler\DefaultChangeHistoryValueGetterHandler;
use common\models\interfaces\IHaveIgnoredOnCopyingAttributes;
use common\models\relation_presenters\OneToManyRelationPresenter;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\drafts\IHasRelations;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;














class ChangeHistoryEntityClass extends ActiveRecord implements IHasRelations, IHaveIgnoredOnCopyingAttributes
{
    use HtmlPropsEncoder;

    public const CHANGE_TYPE_INSERT = 1;
    public const CHANGE_TYPE_DELETE = 2;
    public const CHANGE_TYPE_UPDATE = 3;


    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    


    public static function tableName()
    {
        return '{{%change_history_entity_class}}';
    }

    


    public function rules()
    {
        return [
            [['change_id', 'entity_classifier_id', 'change_type', 'entity_id'], 'integer'],
            [['entity_identifier'], 'string', 'max' => 1000],
            [['change_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChangeHistory::class, 'targetAttribute' => ['change_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'change_id' => 'Change ID',
            'entity_classifier_id' => 'Entity Classifier ID',
            'change_type' => 'Change Type',
            'entity_id' => 'Entity ID',
        ];
    }

    




    public function getChange()
    {
        return $this->hasOne(ChangeHistory::class, ['id' => 'change_id']);
    }

    public function setChangeHistory(ChangeHistory $change)
    {
        $this->change_id = $change->id;
    }

    




    public function getChangeHistoryEntityClassInputs()
    {
        return $this->hasMany(ChangeHistoryEntityClassInput::class, ['entity_class_id' => 'id']);
    }

    



    public function getHistoryValueGetter(ChangeLoggedModelInterface $entity): DefaultChangeHistoryValueGetterHandler
    {
        return new DefaultChangeHistoryValueGetterHandler($entity);
    }

    public function beforeDelete()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $errorFrom = '';
            $deleteSuccess = true;
            $changeHistoryEntityClassInputs = $this->changeHistoryEntityClassInputs;
            if (!empty($changeHistoryEntityClassInputs)) {
                foreach ($changeHistoryEntityClassInputs as $dataToDelete) {
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

    public function getRelationsInfo(): array
    {
        return [
            new OneToManyRelationPresenter('changeHistoryEntityClassInputs', [
                'parent_instance' => $this,
                'child_class' => ChangeHistoryEntityClassInput::class,
                'child_column_name' => 'entity_class_id',
                'actual_relation_name' => 'changeHistoryEntityClassInputs',
                'find_exists_child' => false,
                'make_new_child' => true,
                'ignore_in_comparison' => true,
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
