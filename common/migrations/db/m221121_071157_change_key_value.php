<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221121_071157_change_key_value extends MigrationWithDefaultOptions
{
    private const TN = '{{%key_storage_item}}';

    


    public function safeUp()
    {
        $this->update(
            self::TN,
            [
                'value' => 'navbar-dark bg-lightblue',
                'updated_at' => time(),
                'created_at' => time(),
            ],
            ['key' => 'backend.theme-skin']
        );

        $this->update(
            self::TN,
            [
                'value' => '1',
                'updated_at' => time(),
                'created_at' => time(),
            ],
            ['key' => 'backend.layout-fixed']
        );

        $this->insert(self::TN, [
            'key' => 'backend.logo-skin',
            'value' => 'bg-lightblue',
            'updated_at' => time(),
            'created_at' => time(),
        ]);

        $this->insert(self::TN, [
            'key' => 'backend.dark-mode',
            'value' => '0',
            'updated_at' => time(),
            'created_at' => time(),
        ]);

        $this->insert(self::TN, [
            'key' => 'backend.nav-style',
            'value' => 'nav-flat',
            'updated_at' => time(),
            'created_at' => time(),
        ]);

        $this->insert(self::TN, [
            'key' => 'backend.nav-compact',
            'value' => '0',
            'updated_at' => time(),
            'created_at' => time(),
        ]);

        $this->insert(self::TN, [
            'key' => 'backend.small-body-text',
            'value' => '1',
            'updated_at' => time(),
            'created_at' => time(),
        ]);

        $this->insert(self::TN, [
            'key' => 'backend.nav-child-indent',
            'value' => '0',
            'updated_at' => time(),
            'created_at' => time(),
        ]);
    }

    


    public function safeDown()
    {
        $this->delete(self::TN, ['key' => 'backend.logo-skin']);
        $this->delete(self::TN, ['key' => 'backend.dark-mode']);
        $this->delete(self::TN, ['key' => 'backend.nav-style']);
        $this->delete(self::TN, ['key' => 'backend.nav-compact']);
        $this->delete(self::TN, ['key' => 'backend.small-body-text']);
        $this->delete(self::TN, ['key' => 'backend.nav-child-indent']);
    }
}
