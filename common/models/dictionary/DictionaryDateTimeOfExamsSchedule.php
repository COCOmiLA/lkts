<?php

namespace common\models\dictionary;

use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEventTypeReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubdivisionReferenceType;
use common\models\ModelLinkedToReferenceType;
use common\models\traits\ArchiveTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;


























class DictionaryDateTimeOfExamsSchedule extends ModelLinkedToReferenceType
{
    use ArchiveTrait;

    public const DATE_FORMAT_FOR_LK = 'd.m.Y H:i';
    public const DATE_FORMAT_FOR_1C = 'Y-m-d\TH:i:s';
    public const EMPTY_DATE = '0001-01-01T00:00:00';

    


    public static function tableName()
    {
        return '{{%dictionary_date_time_of_exams_schedule}}';
    }

    protected static $refColumns = [
        'campaign_ref_id' => 'CampaignRef',
        'event_type_ref_id' => 'EventTypeRef',
        'class_room_ref_id' => 'ClassroomRef',
    ];

    protected static $refAdditionalClasses = [
        'campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'event_type_ref_id' => StoredEventTypeReferenceType::class,
        'class_room_ref_id' => StoredSubdivisionReferenceType::class,
    ];

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'end_date',
                    'created_at',
                    'start_date',
                    'updated_at',
                    'campaign_ref_id',
                    'event_type_ref_id',
                    'registration_date',
                ],
                'integer'
            ],
            [
                ['archive',],
                'boolean'
            ],
            [
                [
                    'predmet_guid',
                    'guid_date_time',
                ],
                'string',
                'max' => 100
            ],
            [
                ['note'],
                'string',
                'max' => 500
            ],
            [
                ['campaign_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredAdmissionCampaignReferenceType::class,
                'targetAttribute' => ['campaign_ref_id' => 'id']
            ],
            [
                ['event_type_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredEventTypeReferenceType::class,
                'targetAttribute' => ['event_type_ref_id' => 'id']
            ],
            [
                ['class_room_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredSubdivisionReferenceType::class,
                'targetAttribute' => ['class_room_ref_id' => 'id']
            ],
        ];
    }

    


    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'campaign_ref_id']);
    }

    




    public function getEventTypeRef()
    {
        return $this->hasOne(StoredEventTypeReferenceType::class, ['id' => 'event_type_ref_id']);
    }

    




    public function getClassRoomRef()
    {
        return $this->hasOne(StoredSubdivisionReferenceType::class, ['id' => 'class_room_ref_id']);
    }

    




    public function getPredmetOfExamsSchedules()
    {
        return $this->hasMany(DictionaryPredmetOfExamsSchedule::class, ['predmet_guid' => 'predmet_guid'])
            ->active();
    }

    


    public function getStartDate(): int
    {
        return $this->getDateTimeParamByName('start_date');
    }

    


    public function setStartDate(string $value): void
    {
        $this->setDateTimeParamByName('start_date', $value);
    }

    


    public function getEndDate(): int
    {
        return $this->getDateTimeParamByName('end_date');
    }

    


    public function setEndDate(string $value): void
    {
        $this->setDateTimeParamByName('end_date', $value);
    }

    


    public function getRegistrationDate(): int
    {
        return $this->getDateTimeParamByName('registration_date');
    }

    


    public function setRegistrationDate(string $value): void
    {
        $this->setDateTimeParamByName('registration_date', $value);
    }

    




    private function getDateTimeParamByName(string $name): int
    {
        return $this->{$name};
    }

    





    private function setDateTimeParamByName(string $name, string $value): void
    {
        if (empty($value)) {
            $this->{$name} = 0;
            return;
        }
        $timestamp = strtotime($value);
        if ($timestamp <= 0) {
            $this->{$name} = 0;
            return;
        }

        $this->{$name} = $timestamp;
    }
}
