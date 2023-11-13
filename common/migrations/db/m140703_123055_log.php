<?php
require(Yii::getAlias('@yii/log/migrations/m141106_185632_log_init.php'));

class m140703_123055_log extends m141106_185632_log_init
{
    public function up()
    {
        foreach ($this->getDbTargets() as $target) {
            $this->db = $target->db;

            $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
                
                $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            }

            $this->createTable($target->logTable, [
                'id' => $this->bigPrimaryKey(),
                'level' => $this->integer(),
                'category' => $this->string(),
                'log_time' => $this->double(),
                'prefix' => $this->text(),
                'message' => $this->text(),
            ], $tableOptions);

            $raw_table = str_replace('{{%', '', $target->logTable);
            $raw_table = str_replace('}}', '', $raw_table);
            $this->createIndex("idx_{$raw_table}_log_level", $target->logTable, 'level');
            $this->createIndex("idx_{$raw_table}_log_category", $target->logTable, 'category');
        }
    }
}
