<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\EntrantTestManager\ExamsScheduleManager;
use common\models\dictionary\DictionaryDateTimeOfExamsSchedule;
use common\models\dictionary\DictionaryPredmetOfExamsSchedule;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEventTypeReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubdivisionReferenceType;
use common\models\interfaces\ArchiveModelInterface;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\relation_presenters\OneToOneRelationPresenter;
use common\models\traits\ArchiveTrait;
use common\models\traits\HasDirtyAttributesTrait;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
























class BachelorDatePassingEntranceTest extends ChangeHistoryDecoratedModel implements
    ApplicationConnectedInterface,
    ArchiveModelInterface,
    ChangeLoggedModelInterface,
    ICanGivePropsToCompare,
    IHasRelations,
    IHaveIdentityProp
{
    use ArchiveTrait;
    use HasDirtyAttributesTrait;

    
    public $scheduleLabel = '';

    
    public $existGuidDateTimeList = [];

    
    public $relationList = [];

    
    public $hasChildren = false;

    


    public static function tableName()
    {
        return '{{%bachelor_date_passing_entrance_test}}';
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
                    'bachelor_egeresult_id',
                    'date_time_of_exams_schedule_id',
                    'created_at',
                    'updated_at',
                    'archived_at',
                ],
                'integer'
            ],
            [
                [
                    'from_1c',
                    'archive',
                ],
                'boolean'
            ],
            [
                ['bachelor_egeresult_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => EgeResult::class,
                'targetAttribute' => ['bachelor_egeresult_id' => 'id']
            ],
            [
                ['date_time_of_exams_schedule_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => DictionaryDateTimeOfExamsSchedule::class,
                'targetAttribute' => ['date_time_of_exams_schedule_id' => 'id']
            ],
            [
                ['parent_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => BachelorDatePassingEntranceTest::class,
                'targetAttribute' => ['parent_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'date_time_of_exams_schedule_id' => $this->dateTimeOfExamsSchedule ?
                BachelorDatePassingEntranceTest::humanizer(ArrayHelper::getValue($this, 'dateTimeOfExamsSchedule.eventTypeRef.reference_name')) :
                $this->scheduleLabel,
            'from_1c' => 'Из 1c',
        ];
    }

    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchivedAtColumn(): string
    {
        return 'archived_at';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    




    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id'])
            ->via('rawEgeResult');
    }

    




    public function getEgeResult()
    {
        return $this->getRawEgeResult()->active();
    }

    




    public function getRawEgeResult()
    {
        return $this->hasOne(EgeResult::class, ['id' => 'bachelor_egeresult_id']);
    }

    




    public function getDateTimeOfExamsSchedule()
    {
        return $this->hasOne(DictionaryDateTimeOfExamsSchedule::class, ['id' => 'date_time_of_exams_schedule_id']);
    }

    




    public function getRawParent()
    {
        return $this->hasOne(BachelorDatePassingEntranceTest::class, ['id' => 'parent_id']);
    }

    




    public function getParent()
    {
        return $this->getRawParent()
            ->active();
    }

    


    public function getRawChildren()
    {
        return $this->hasOne(BachelorDatePassingEntranceTest::class, ['parent_id' => 'id']);
    }

    


    public function getChildren()
    {
        return $this->getRawChildren()
            ->active();
    }

    




    public function getOrCreateChildren()
    {
        $result = $this->getChildren()->one();
        if (empty($result)) {
            $result = new BachelorDatePassingEntranceTest();
            $result->from_1c = false;

            if ($this->bachelor_egeresult_id) {
                $result->bachelor_egeresult_id = $this->bachelor_egeresult_id;
            }
            if ($this->id) {
                $result->parent_id = $this->id;
            }

            return $result;
        }


        return $result;
    }

    




    public static function generateName($data)
    {
        $endDate = date(DictionaryDateTimeOfExamsSchedule::DATE_FORMAT_FOR_LK, $data['end_date']);
        $startDate = date(DictionaryDateTimeOfExamsSchedule::DATE_FORMAT_FOR_LK, $data['start_date']);
        $result = Yii::t(
            'abiturient/bachelor/ege/bachelor-date-passing-entrance-test',
            'Текст для элемента выпадающего списка расписания экзаменов; формы ВИ: `Дата начала: {startDate}, дата окончания: {endDate}`',
            [
                'endDate' => $endDate,
                'startDate' => $startDate,
            ]
        );

        if (!empty($data['class_room_name'])) {
            $classRoomName = $data['class_room_name'];
            $result .= Yii::t(
                'abiturient/bachelor/ege/bachelor-date-passing-entrance-test',
                'Дополнительный текст для элемента выпадающего списка расписания экзаменов, если заполнено место (аудитория) проведения ВИ; формы ВИ: ` ({classRoomName})`',
                ['classRoomName' => $classRoomName]
            );
        }

        return $result;
    }

    


    public static function getExamScheduleBaseQuery()
    {
        
        $tnStoredEventTypeReferenceType = StoredEventTypeReferenceType::tableName();
        $tnStoredSubdivisionReferenceType = StoredSubdivisionReferenceType::tableName();

        
        $tnDictionaryDateTimeOfExamsSchedule = DictionaryDateTimeOfExamsSchedule::tableName();
        return (new Query)
            ->select([
                "{$tnDictionaryDateTimeOfExamsSchedule}.id",
                "{$tnDictionaryDateTimeOfExamsSchedule}.end_date",
                "{$tnStoredEventTypeReferenceType}.reference_name",
                "{$tnDictionaryDateTimeOfExamsSchedule}.start_date",
                "{$tnDictionaryDateTimeOfExamsSchedule}.predmet_guid",
                "{$tnDictionaryDateTimeOfExamsSchedule}.guid_date_time",
                "{$tnDictionaryDateTimeOfExamsSchedule}.registration_date",
                "{$tnStoredSubdivisionReferenceType}.reference_name AS class_room_name",
            ])
            ->from($tnDictionaryDateTimeOfExamsSchedule)
            ->leftJoin(
                $tnStoredEventTypeReferenceType,
                "{$tnDictionaryDateTimeOfExamsSchedule}.event_type_ref_id = {$tnStoredEventTypeReferenceType}.id"
            )
            ->leftJoin(
                $tnStoredSubdivisionReferenceType,
                "{$tnDictionaryDateTimeOfExamsSchedule}.class_room_ref_id = {$tnStoredSubdivisionReferenceType}.id"
            )
            ->andWhere(["{$tnDictionaryDateTimeOfExamsSchedule}.archive" => false]);
    }

    


    public static function getExamScheduleBaseDetalizedQuery()
    {
        
        $tnStoredDisciplineReferenceType = StoredDisciplineReferenceType::tableName();
        $tnStoredDisciplineFormReferenceType = StoredDisciplineFormReferenceType::tableName();

        
        $tnSpeciality = Speciality::tableName();
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnDictionaryPredmetOfExamsSchedule = DictionaryPredmetOfExamsSchedule::tableName();
        $tnDictionaryDateTimeOfExamsSchedule = DictionaryDateTimeOfExamsSchedule::tableName();
        return BachelorDatePassingEntranceTest::getExamScheduleBaseQuery()
            
            
            ->leftJoin(
                $tnDictionaryPredmetOfExamsSchedule,
                "{$tnDictionaryDateTimeOfExamsSchedule}.predmet_guid = {$tnDictionaryPredmetOfExamsSchedule}.predmet_guid"
            )
            ->leftJoin(
                $tnStoredDisciplineReferenceType,
                "{$tnDictionaryPredmetOfExamsSchedule}.subject_ref_id = {$tnStoredDisciplineReferenceType}.id"
            )
            ->leftJoin(
                $tnStoredDisciplineFormReferenceType,
                "{$tnDictionaryPredmetOfExamsSchedule}.form_ref_id = {$tnStoredDisciplineFormReferenceType}.id"
            )

            
            ->leftJoin(
                $tnSpeciality,
                
                "{$tnDictionaryPredmetOfExamsSchedule}.campaign_ref_id = {$tnSpeciality}.campaign_ref_id AND " .
                
                "{$tnDictionaryPredmetOfExamsSchedule}.curriculum_ref_id = {$tnSpeciality}.curriculum_ref_id AND " .
                
                "{$tnDictionaryPredmetOfExamsSchedule}.group_ref_id = {$tnSpeciality}.competitive_group_ref_id AND " .
                
                "{$tnDictionaryPredmetOfExamsSchedule}.finance_ref_id = {$tnSpeciality}.education_source_ref_id"
            )

            
            ->leftJoin(
                $tnBachelorSpeciality,
                "{$tnSpeciality}.id = {$tnBachelorSpeciality}.speciality_id"
            );
    }

    




    public function getExamScheduleList($predmetGUIDs = [])
    {
        $relationList = [];
        $guidDateTimeList = [];

        $tnDictionaryDateTimeOfExamsSchedule = DictionaryDateTimeOfExamsSchedule::tableName();

        $dates = BachelorDatePassingEntranceTest::getExamScheduleBaseQuery()
            ->andWhere(['in', 'predmet_guid', $predmetGUIDs])
            ->andWhere(["{$tnDictionaryDateTimeOfExamsSchedule}.campaign_ref_id" => $this->application->type->rawCampaign->ref_id])
            ->orderBy(["{$tnDictionaryDateTimeOfExamsSchedule}.start_date" => SORT_ASC])
            ->all();

        $zeroKey = array_key_first($dates);
        $this->scheduleLabel = BachelorDatePassingEntranceTest::humanizer($dates[$zeroKey]['reference_name']);

        $examDateList = [];
        foreach ($dates as $data) {
            

            $searchIndex = array_search($data['predmet_guid'], $predmetGUIDs);
            if ($searchIndex !== false) {
                if (!array_key_exists($searchIndex, $relationList)) {
                    $relationList[$searchIndex] = [];
                }
                $relationList[$searchIndex][] = $data['id'];
            }
            $guidDateTimeList[$data['id']] = $data['guid_date_time'];

            $examDateList[$data['id']] = BachelorDatePassingEntranceTest::generateName($data);
        }

        if (!empty($guidDateTimeList)) {
            $this->existGuidDateTimeList = $guidDateTimeList;
            $this->hasChildren = DictionaryDateTimeOfExamsSchedule::find()
                ->where(['in', 'predmet_guid', $guidDateTimeList])
                ->exists();
        }

        $this->relationList = $relationList;

        return $examDateList;
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        $children = $this->children;
        if ($children) {
            return $children->delete();
        }

        return true;
    }

    public function beforeArchive()
    {
        if (!ExamsScheduleManager::archiveIfExist($this->children)) {
            Yii::error('Ошибка архивирования расписания сдачи ВИ', 'BachelorDatePassingEntranceTest.beforeArchive');
            return false;
        }

        return true;
    }

    public function getRelationsInfo(): array
    {
        return [
            new OneToOneRelationPresenter(
                'children',
                [
                    'parent_instance' => $this,
                    'child_class' => BachelorDatePassingEntranceTest::class,
                    'child_column_name' => 'parent_id',
                    'parent_column_name' => 'id',
                ]
            ),
        ];
    }

    








    public static function humanizer(?string $textToHumanize): string
    {
        if (empty($textToHumanize)) {
            return '';
        }
        $textToHumanize = trim((string)$textToHumanize);
        $textToHumanize = Inflector::camel2words($textToHumanize);
        return mb_strtoupper(
            mb_substr(
                $textToHumanize,
                0,
                1
            )
        ) . mb_strtolower(
            mb_substr(
                $textToHumanize,
                1,
                mb_strlen((string)$textToHumanize)
            )
        );
    }

    public function getPropsToCompare(): array
    {
        return [
            'parent_id',
            'bachelor_egeresult_id',
            'date_time_of_exams_schedule_id'
        ];
    }

    


    public function getIdentityString(): string
    {
        return implode('_', $this->getIdentityList());
    }

    




    public function getIdentityList(bool $forHuman = false): array
    {
        $refValue = 'reference_uid';
        if ($forHuman) {
            $refValue = 'reference_name';
        }
        $parentRefUid = ArrayHelper::getValue($this, "parent.dateTimeOfExamsSchedule.eventTypeRef.{$refValue}", null);
        $dateTimeOfExamsScheduleRefUid = ArrayHelper::getValue($this, "dateTimeOfExamsSchedule.eventTypeRef.{$refValue}", null);

        return array_filter(
            [$parentRefUid, $dateTimeOfExamsScheduleRefUid],
            function ($item) {
                return isset($item);
            }
        );
    }

    public function getChangeLoggedAttributes()
    {
        return ['date_time_of_exams_schedule_id'];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_ENTRANCE_TEST_DATE_PASSING;
    }

    public function getEntityIdentifier(): ?string
    {
        return ArrayHelper::getValue($this, 'rawEgeResult.disciplineReferenceName') .
            ' (' . BachelorDatePassingEntranceTest::humanizer(ArrayHelper::getValue($this, 'dateTimeOfExamsSchedule.eventTypeRef.reference_name')) . ')';
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_ENTRANCE_TEST_DATE_PASSING;
    }
}
