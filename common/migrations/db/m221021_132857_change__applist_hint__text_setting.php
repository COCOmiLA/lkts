<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221021_132857_change__applist_hint__text_setting extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';

    private const TEXT_NAME = 'applist_hint';

    private const TRANSFORMATION_TABLE = [
        1 => 'first',
        2 => 'second',
        3 => 'third',
    ];

    


    public function safeUp()
    {
        foreach (self::TRANSFORMATION_TABLE as $order => $namePrefix) {
            $this->update(
                self::TN,
                ['name' => $namePrefix . '_' . self::TEXT_NAME],
                [
                    'name' => self::TEXT_NAME,
                    'order' => $order
                ]
            );
        }

        $this->dropColumn(self::TN, 'order');

        $this->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->addColumn(self::TN, 'order', $this->integer()->defaultValue(0));

        $this->db->schema->refresh();

        foreach (self::TRANSFORMATION_TABLE as $order => $namePrefix) {
            $this->update(
                self::TN,
                [
                    'name' => self::TEXT_NAME,
                    'order' => $order
                ],
                ['name' => $namePrefix . '_' . self::TEXT_NAME]
            );
        }
    }
}
