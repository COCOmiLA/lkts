<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221220_104925_delete_needless_text_settings extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->delete('{{%text_settings}}', [
            'name' => [
                'ege_approved_sandbox_off',
                'ege_approved_sandbox_on',
                'ege_rejected_by1c',
                'sandbox_1c_error'
            ]
        ]);
    }
}
