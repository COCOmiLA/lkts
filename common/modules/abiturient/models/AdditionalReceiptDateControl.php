<?php

namespace common\modules\abiturient\models;

use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\ModelLinkedToReferenceType;
use common\models\traits\ScenarioWithoutExistValidationTrait;















class AdditionalReceiptDateControl extends ModelLinkedToReferenceType
{
    use ScenarioWithoutExistValidationTrait;

    public function rules()
    {
        return [
            [
                [
                    'campaign_ref_id',
                    'stage',
                    'competitive_group_ref_id',
                ],
                'integer'
            ],
            [
                [
                    'date_start',
                    'date_end',
                ],
                'string'
            ],
            [
                ['campaign_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredAdmissionCampaignReferenceType::class,
                'targetAttribute' => ['campaign_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['competitive_group_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredCompetitiveGroupReferenceType::class,
                'targetAttribute' => ['competitive_group_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
        ];
    }

    protected static $refAdditionalClasses = [
        'campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'competitive_group_ref_id' => StoredCompetitiveGroupReferenceType::class,
    ];

    protected static $refColumns = [
        'campaign_ref_id' => 'CampaignRef',
        'competitive_group_ref_id' => 'CompetitiveGroupRef',
    ];

    public function getCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'campaign_ref_id']);
    }

    public function getCompetitiveGroupRef()
    {
        return $this->hasOne(StoredCompetitiveGroupReferenceType::class, ['id' => 'competitive_group_ref_id']);
    }

    public function getDate_start_timestamp(): int
    {
        if ($this->date_start) {
            return strtotime($this->date_start);
        }
        return 0;
    }

    public function getDate_end_timestamp(): int
    {
        if ($this->date_end) {
            return strtotime($this->date_end);
        }
        return 0;
    }

    public function getFormatted_date_start(): string
    {
        return date('d.m.Y', $this->date_start_timestamp);
    }

    public function getFormatted_date_end(): string
    {
        return date('d.m.Y', $this->date_end_timestamp);
    }

    public static function tableName()
    {
        return '{{%additional_receipt_date_controls}}';
    }
}