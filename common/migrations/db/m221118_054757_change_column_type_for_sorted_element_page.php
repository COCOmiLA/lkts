<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221118_054757_change_column_type_for_sorted_element_page extends MigrationWithDefaultOptions
{
    private const TN = '{{%sorted_element_page}}';

    


    public function safeUp()
    {
        $this->update(
            self::TN,
            ['place' => 'left'],
            [
                'OR',
                ['place' => 'student'],
                ['place' => ''],
            ]
        );
        $this->update(
            self::TN,
            ['place' => 'right'],
            ['place' => '1'],
        );

        $this->alterColumn(
            self::TN,
            'place',
            $this->string(6)->defaultValue('left')
        );

        $this->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->update(
            self::TN,
            ['place' => ''],
            ['place' => 'left'],
        );
        $this->update(
            self::TN,
            ['place' => '1'],
            ['place' => 'right'],
        );

        $this->alterColumn(
            self::TN,
            'place',
            $this->string(512)->defaultValue('student')
        );

        $this->db->schema->refresh();
    }
}
