<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\MaxSpecialityType;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use yii\db\Query;




class m221118_122434_drop_max_speciality_type_codes extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->delete('{{%code_settings}}', ['name' => [
            'max_speciality_type.speciality',
            'max_speciality_type.group',
            'max_speciality_type.faculty'
        ]]);
        
        if ($this->db->getTableSchema(AdmissionCampaign::tableName())->getColumn('contest_allowed')) {
            $this->renameColumn(AdmissionCampaign::tableName(), 'contest_allowed', 'consents_allowed');
        }
        if (!$this->db->getTableSchema(AdmissionCampaign::tableName())->getColumn('count_target_specs_separately')) {
            $this->addColumn(AdmissionCampaign::tableName(), 'count_target_specs_separately', $this->boolean()->defaultValue(false));
        }
    }

    


    public function safeDown()
    {
        $this->dropColumn(AdmissionCampaign::tableName(), 'count_target_specs_separately');

        $this->renameColumn(\common\modules\abiturient\models\bachelor\AdmissionCampaign::tableName(), 'consents_allowed', 'contest_allowed');

        if (!$this->codeExists('max_speciality_type.speciality')) {
            $this->insert('{{%code_settings}}', [
                'name' => 'max_speciality_type.speciality',
                'description' => 'Тип ограничения количества направлений "По направлениям подготовки"',
                'value' => MaxSpecialityType::TYPE_SPECIALITY
            ]);
        }

        if (!$this->codeExists('max_speciality_type.group')) {
            $this->insert('{{%code_settings}}', [
                'name' => 'max_speciality_type.group',
                'description' => 'Тип ограничения количества направлений "По конкурсным группам"',
                'value' => MaxSpecialityType::TYPE_GROUP
            ]);
        }

        if (!$this->codeExists('max_speciality_type.faculty')) {
            $this->insert('{{%code_settings}}', [
                'name' => 'max_speciality_type.faculty',
                'description' => 'Тип ограничения количества направлений "По факультетам"',
                'value' => MaxSpecialityType::TYPE_FACULTY
            ]);
        }
    }

    protected function codeExists($name): bool
    {
        return (new Query())
            ->select('id')
            ->from('{{%code_settings}}')
            ->where(['name' => $name])
            ->exists();
    }
}
