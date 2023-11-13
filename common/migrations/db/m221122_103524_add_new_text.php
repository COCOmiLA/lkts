<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;




class m221122_103524_add_new_text extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text = new TextSetting();
        $text->attributes = [
            'name' => 'add_previous_passports_text',
            'description' => 'Текст сообщения о необходимости заполнить информацию о предыдущих паспортах поступающего',
            'value' => 'Просим указывать данные всех документов, удостоверяющих личность (в том числе предыдущих). Для паспорта РФ также просим прикреплять скан-копию 19 страницы. Это необходимо для корректной проверки результатов ЕГЭ.',
            'category' => TextSetting::CATEGORY_QUESTIONARY,
        ];
        $text->save(false);
        if (!$this->db->getTableSchema(AdmissionCampaign::tableName())->getColumn('require_previous_passport')) {
            $this->addColumn(AdmissionCampaign::tableName(), 'require_previous_passport', $this->boolean()->defaultValue(false));
        }

        if (!$this->db->getTableSchema(AbiturientQuestionary::tableName())->getColumn('have_no_previous_passport')) {
            $this->addColumn(AbiturientQuestionary::tableName(), 'have_no_previous_passport', $this->boolean()->defaultValue(false));
        }
    }

    


    public function safeDown()
    {
        $this->dropColumn(AbiturientQuestionary::tableName(), 'have_no_previous_passport');
        $this->dropColumn(AdmissionCampaign::tableName(), 'require_previous_passport');

        $to_delete = TextSetting::findOne([
            'name' => 'add_previous_passports_text',
        ]);
        if ($to_delete) {
            $to_delete->delete();
        }
    }
}
