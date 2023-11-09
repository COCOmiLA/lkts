<?php

use yii\db\Migration;




class m211015_091527_add_parent_id_column_to_bachelor_date_passing_entrance_test_table extends Migration
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_date_passing_entrance_test}}', 'parent_id', $this->integer()->defaultValue(null));

        $this->addForeignKey(
            'FK_to_self_from_self',
            '{{%bachelor_date_passing_entrance_test}}',
            'parent_id',
            '{{%bachelor_date_passing_entrance_test}}',
            'id'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_to_self_from_self', '{{%bachelor_date_passing_entrance_test}}');

        $this->dropColumn('{{%bachelor_date_passing_entrance_test}}', 'parent_id');
    }
}
