<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\migrations\traits\TableOptionsTrait;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;
use common\models\settings\CodeSetting;




class m210516_165014_add_education_ref_table extends MigrationWithDefaultOptions
{
    use TableOptionsTrait;
    use createDropReferenceTable;
    


    public function safeUp()
    {
        $this->createReferenceTable('education_reference_type', self::GetTableOptions());
        $this->addColumn('{{%education_data}}', 'education_ref_id', $this->integer());
        $this->createIndex(
            '{{%idx-education_data-education_ref_id}}',
            '{{%education_data}}',
            'education_ref_id'
        );

        $this->addForeignKey(
            '{{%fk-education_data-education_ref_id}}',
            '{{%education_data}}',
            'education_ref_id',
            '{{%education_reference_type}}',
            'id',
            'NO ACTION'
        );

        $setting = CodeSetting::findOne(['name' => 'allow_multiple_educations']);
        if(!empty($setting)) {
            $setting->delete();
        }
    }

    


    public function safeDown()
    {
        $setting = new CodeSetting();
        $setting->value = 0;
        $setting->name = 'allow_multiple_educations';
        $setting->description = 'Сможет ли поступающий указывать несколько документов об образовании для каждого выбранного направления подготовки';
        $setting->save();

        $this->dropForeignKey(
            '{{%fk-education_data-education_ref_id}}',
            '{{%education_data}}'
        );

        $this->dropIndex(
            '{{%idx-education_data-education_ref_id}}',
            '{{%education_data}}'
        );


        $this->dropColumn('{{%education_data}}', 'education_ref_id');

        if (Yii::$app->db->schema->getTableSchema('education_reference_type') !== null) {
            $this->dropReferenceTable('education_reference_type');
        }
    }

}
