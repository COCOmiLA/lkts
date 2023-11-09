<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221005_092428_alter_tables_collation extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci';
            
            $this->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();

            $this->alterTable('additional_receipt_date_controls', $tableOptions);
            $this->alterTable('bachelor_preferences', $tableOptions);
            $this->alterTable('bachelor_target_reception', $tableOptions);
            $this->alterTable('comments_coming', $tableOptions);
            $this->alterTable('dictionary_budget_level', $tableOptions);
            $this->alterTable('dictionary_family_type', $tableOptions);
            $this->alterTable('dummy_soap_response', $tableOptions);
            $this->alterTable('foreign_languages_for_ege', $tableOptions);
            $this->alterTable('individual_achievements_document_types', $tableOptions);
            $this->alterTable('oauth_access_tokens', $tableOptions);
            $this->alterTable('oauth_authorization_codes', $tableOptions);
            $this->alterTable('oauth_clients', $tableOptions);
            $this->alterTable('oauth_jwt', $tableOptions);
            $this->alterTable('oauth_public_keys', $tableOptions);
            $this->alterTable('oauth_refresh_tokens', $tableOptions);
            $this->alterTable('oauth_scopes', $tableOptions);
            $this->alterTable('oauth_users', $tableOptions);
            $this->alterTable('parent_data', $tableOptions);
            $this->alterTable('portal_database_version', $tableOptions);
            $this->alterTable('recaptcha', $tableOptions);
            $this->alterTable('agreement_info', $tableOptions);


            
            $this->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();
        }
    }

    private function alterTable($tableName, $tableOptions)
    {
        if ($this->db->getTableSchema($tableName) !== null) {
            $this->db->createCommand("ALTER TABLE `{$tableName}` CONVERT TO {$tableOptions}")->execute();
        }
    }
}
