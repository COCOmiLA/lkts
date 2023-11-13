<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200619_123543_change_location_to_tables extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        if (\Yii::$app->db->driverName === 'mysql') {
            $this->execute("ALTER TABLE bachelor_preferences CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
            $this->execute("ALTER TABLE bachelor_target_reception CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
            $this->execute("ALTER TABLE individual_achievements_document_types CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
            $this->execute("ALTER TABLE dictionary_budget_level CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
            $this->execute("ALTER TABLE foreign_languages_for_ege CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
        }
    }

    


    public function down()
    {
        echo "m200619_123543_change_location_to_tables cannot be reverted.\n";

        return false;
    }

    













}
