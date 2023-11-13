<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221111_124916_add_column_to_bachelor_date_passing_entrance_test extends MigrationWithDefaultOptions
{
    private const TN = '{{%dictionary_date_time_of_exams_schedule}}';
    private const CLASS_ROOM_TN = '{{%subdivision_reference_type}}';

    


    public function safeUp()
    {
        $this->addColumn(self::TN, 'class_room_ref_id', $this->integer()->defaultValue(null));

        $this->addForeignKey(
            'FK_to_DDTOES_from_class_room_ref',
            self::TN,
            'class_room_ref_id',
            SELF::CLASS_ROOM_TN,
            'id',
            'SET NULL',
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_to_DDTOES_from_class_room_ref', self::TN);

        $this->dropColumn(self::TN, 'class_room_ref_id');
    }
}
