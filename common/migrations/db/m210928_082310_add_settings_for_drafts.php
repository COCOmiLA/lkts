<?php

use common\models\settings\CodeSetting;
use yii\db\Migration;




class m210928_082310_add_settings_for_drafts extends Migration
{
    


    public function safeUp()
    {
        
        $setting = CodeSetting::findOne([
            'name' => 'block_application_after_approve'
        ]);
        if ($setting) {
            $setting->delete();
        }

        $this->addColumn('{{%application_type}}', 'moderator_allowed_to_edit', $this->boolean()->defaultValue(true));
        $this->addColumn('{{%application_type}}', 'persist_moderators_changes_in_sent_application', $this->boolean()->defaultValue(false));

        Yii::$app->db->schema->refresh();

    }

    


    public function safeDown()
    {
        $setting = new CodeSetting();
        $setting->value = 0;
        $setting->name = 'block_application_after_approve';
        $setting->description = 'Блокировать редактирование заявления (данные об образовании, редактирование льгот, преимущественных прав, целевых, редактирование индивидуальных достижений) после первого одобрения.';
        $setting->save();

        $this->dropColumn('{{%application_type}}', 'moderator_allowed_to_edit');
        $this->dropColumn('{{%application_type}}', 'persist_moderators_changes_in_sent_application');

        Yii::$app->db->schema->refresh();
    }
}
