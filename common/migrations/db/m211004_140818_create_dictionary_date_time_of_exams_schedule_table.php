<?php

use common\components\migrations\traits\TableOptionsTrait;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;
use yii\db\Migration;




class m211004_140818_create_dictionary_date_time_of_exams_schedule_table extends Migration
{
    use TableOptionsTrait;
    use createDropReferenceTable;

    private const TN_DIC_DATE_TIME_OF_EXAMS_SCHEDULE = '{{%dictionary_date_time_of_exams_schedule}}';

    private const TN_EVENT_TYPE_REF_TYPE = '{{%event_type_reference_type}}';
    private const TN_ADMISSION_CAMPAIGN_REF_TYPE = '{{%admission_campaign_reference_type}}';

    


    public function safeUp()
    {
        $this->createTable(
            self::TN_DIC_DATE_TIME_OF_EXAMS_SCHEDULE,
            [
                'id' => $this->primaryKey(),

                'campaign_ref_id' => $this->integer()->defaultValue(null),
                'event_type_ref_id' => $this->integer()->defaultValue(null),
                'predmet_guid' => $this->string(100)->defaultValue(null),
                'guid_date_time' => $this->string(100)->defaultValue(null),
                'start_date' => $this->integer()->defaultValue(null),
                'end_date' => $this->integer()->defaultValue(null),
                'registration_date' => $this->integer()->defaultValue(null),
                'note' => $this->string(500)->defaultValue(null),

                'archive' => $this->boolean()->defaultValue(false),

                'created_at' => $this->integer()->defaultValue(null),
                'updated_at' => $this->integer()->defaultValue(null),
            ],
            self::GetTableOptions()
        );

        $this->createReferenceTable(self::TN_EVENT_TYPE_REF_TYPE, self::GetTableOptions());

        $fkList = [
            'campaign_ref' => self::TN_ADMISSION_CAMPAIGN_REF_TYPE,
            'event_type_ref' => self::TN_EVENT_TYPE_REF_TYPE,
        ];

        foreach ($fkList as $column => $table) {
            $this->addForeignKey(
                "FK_to_{$column}_for_dtoes", 
                self::TN_DIC_DATE_TIME_OF_EXAMS_SCHEDULE,
                "{$column}_id",
                $table,
                'id'
            );
        }
    }

    


    public function safeDown()
    {
        $fkList = [
            'campaign_ref_id',
            'event_type_ref',
        ];

        foreach ($fkList as $column) {
            $this->dropForeignKey("FK_to_{$column}_for_dtoes", self::TN_DIC_DATE_TIME_OF_EXAMS_SCHEDULE);
        }

        if (Yii::$app->db->schema->getTableSchema(self::TN_EVENT_TYPE_REF_TYPE) !== null) {
            $this->dropReferenceTable(self::TN_EVENT_TYPE_REF_TYPE);
        }

        $this->dropTable(self::TN_DIC_DATE_TIME_OF_EXAMS_SCHEDULE);
    }
}
