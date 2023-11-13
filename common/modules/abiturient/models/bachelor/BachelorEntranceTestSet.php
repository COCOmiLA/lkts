<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\queries\ArchiveQuery;
use common\models\interfaces\ArchiveModelInterface;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\traits\ArchiveTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;





















class BachelorEntranceTestSet extends ActiveRecord implements
    IHaveIdentityProp,
    ArchiveModelInterface
{
    use ArchiveTrait;

    


    public static function tableName()
    {
        return '{{%bachelor_entrance_test_set}}';
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
                    'priority',
                    'created_at',
                    'updated_at',
                    'archived_at',
                    'bachelor_egeresult_id',
                    'bachelor_speciality_id',
                ],
                'integer'
            ],
            [
                'entrance_test_junction',
                'string',
                'max' => 32
            ],
            [
                ['archive'],
                'boolean'
            ],
            [
                ['bachelor_speciality_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => BachelorSpeciality::class,
                'targetAttribute' => ['bachelor_speciality_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getEgeResultByEntranceTestParamsOnly(): ActiveQuery
    {
        return $this->getRawEgeResultByEntranceTestParamsOnly()
            ->active();
    }

    




    public function getRawEgeResultByEntranceTestParamsOnly(): ActiveQuery
    {
        return $this->hasOne(EgeResult::class, ['entrance_test_junction' => 'entrance_test_junction']);
    }

    




    public function getEgeResult(): ActiveQuery
    {
        return $this->getRawEgeResult()
            ->active();
    }

    




    public function getRawEgeResult(): ActiveQuery
    {
        $query = $this->getRawEgeResultByEntranceTestParamsOnly();

        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnEgeResult = EgeResult::tableName();
        $subQuery = BachelorSpeciality::find()
            ->select("{$tnBachelorSpeciality}.application_id")
            ->andWhere(["{$tnBachelorSpeciality}.id" => $this->bachelor_speciality_id]);

        return $query->andOnCondition(['IN', "{$tnEgeResult}.application_id", $subQuery]);
    }

    




    public function getRawBachelorSpeciality(): ActiveQuery
    {
        return $this->hasOne(BachelorSpeciality::class, ['id' => 'bachelor_speciality_id']);
    }

    




    public function getBachelorSpeciality(): ActiveQuery
    {
        return $this->getRawBachelorSpeciality()
            ->active();
    }

    


    public function getIdentityString(): string
    {
        $tnEgeResult = EgeResult::tableName();

        $bachelorSpeciality = $this->rawBachelorSpeciality;
        $egeResult = $this->getRawEgeResult()
            ->andWhere(["{$tnEgeResult}.application_id" => $bachelorSpeciality->application_id])
            ->one();

        $egeResultIdentityString = $egeResult->identityString;
        $bachelorSpecialityIdentityString = $bachelorSpeciality->identityString;
        return "{$egeResultIdentityString}_{$bachelorSpecialityIdentityString}_{$this->priority}";
    }

    public static function find()
    {
        return new ArchiveQuery(static::class);
    }

    public function beforeArchive()
    {
        $tnEgeResult = EgeResult::tableName();

        $bachelorSpeciality = $this->rawBachelorSpeciality;
        $egeResult = $this->getEgeResult()
            ->andWhere(["{$tnEgeResult}.application_id" => $bachelorSpeciality->application_id])
            ->one();
        if ($egeResult && $egeResult->isOnlyEgeForThisApplication([$this->id])) {
            return $egeResult->archive();
        }

        return true;
    }
}
