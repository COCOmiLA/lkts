<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\base\BaseObject;

require(Yii::getAlias('@yii/rbac/migrations/m170907_052038_rbac_add_index_on_auth_assignment_user_id.php'));
require(Yii::getAlias('@yii/rbac/migrations/m180523_151638_rbac_updates_indexes_without_prefix.php'));
require(Yii::getAlias('@yii/rbac/migrations/m200409_110543_rbac_update_mssql_trigger.php'));




class m140703_123814_setup_rbac_tables extends MigrationWithDefaultOptions
{
    protected $migrations = [
        'm170907_052038_rbac_add_index_on_auth_assignment_user_id',
        'm180523_151638_rbac_updates_indexes_without_prefix',
        'm200409_110543_rbac_update_mssql_trigger',
    ];

    


    public function safeUp()
    {
        foreach ($this->migrations as $migration_class) {
            $migration = Yii::createObject($migration_class);
            if ($migration instanceof BaseObject && $migration->canSetProperty('compact')) {
                $migration->compact = $this->compact;
            }
            if ($migration->up() === false) {
                return false;
            }
        }

        $new_type = $this->integer()->notNull();
        if ($this->db->driverName === 'pgsql') {
            $new_type = $new_type->append('USING CAST(user_id AS integer)');
            $this->dropPrimaryKey('rbac_auth_assignment_pkey', '{{%rbac_auth_assignment}}');
        }
        $this->alterColumn('{{%rbac_auth_assignment}}', 'user_id', $new_type);
        if ($this->db->driverName === 'pgsql') {
            $this->addPrimaryKey('rbac_auth_assignment_pkey', '{{%rbac_auth_assignment}}', ['item_name', 'user_id']);
        }
        return true;
    }

    


    public function safeDown()
    {
        if ($this->db->driverName === 'pgsql') {
            $this->dropPrimaryKey('rbac_auth_assignment_pkey', '{{%rbac_auth_assignment}}');
        }
        $this->alterColumn('{{%rbac_auth_assignment}}', 'user_id', $this->string(64)->notNull());
        if ($this->db->driverName === 'pgsql') {
            $this->addPrimaryKey('rbac_auth_assignment_pkey', '{{%rbac_auth_assignment}}', ['item_name', 'user_id']);
        }
        foreach ($this->migrations as $migration_class) {
            $migration = Yii::createObject($migration_class);
            if ($migration instanceof BaseObject && $migration->canSetProperty('compact')) {
                $migration->compact = $this->compact;
            }
            if ($migration->down() === false) {
                return false;
            }
        }
        return true;
    }
}
