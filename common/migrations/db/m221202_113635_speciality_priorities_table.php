<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;




class m221202_113635_speciality_priorities_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%speciality_priorities}}', [
            'id' => $this->primaryKey()->unsigned(),

            'bachelor_speciality_id' => $this->integer()->notNull(),
            'enrollment_priority' => $this->integer()->notNull()->comment('Приоритет поступления'),
            'inner_priority' => $this->integer()->notNull()->comment('Внутренний приоритет для сортировки'),
            'priority_group_identifier' => $this->string(255)->notNull()->comment('Идентификатор группы приоритетов'),
        ]);
        
        $this->createIndex('idx-speciality_priorities-speciality_id', '{{%speciality_priorities}}', 'bachelor_speciality_id');
        
        $this->addForeignKey('fk-speciality_priorities-speciality_id', '{{%speciality_priorities}}', 'bachelor_speciality_id', BachelorSpeciality::tableName(), 'id', 'CASCADE');

        $this->db->schema->refresh();
        Yii::$app->configurationManager->suspendUnspecifiedCodesError(true);
        foreach (BachelorSpeciality::find()->each() as $speciality) {
            $this->insert('{{%speciality_priorities}}', [
                'bachelor_speciality_id' => $speciality->id,
                'enrollment_priority' => $speciality->priority,
                'priority_group_identifier' => $speciality->speciality_id,
                'inner_priority' => 1,
            ]);
        }
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk-speciality_priorities-speciality_id', '{{%speciality_priorities}}');
        $this->dropIndex('idx-speciality_priorities-speciality_id', '{{%speciality_priorities}}');
        $this->dropTable('{{%speciality_priorities}}');
    }
}
