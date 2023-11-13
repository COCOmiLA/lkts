<?php

use yii\db\Migration;
use yii\db\Query;




class m210921_064400_change_structure_of_recaptcha extends Migration
{
    


    public function safeUp()
    {
        $recaptchaOldSettings = (new Query)
            ->from('recaptcha')
            ->one();

        if (empty($recaptchaOldSettings)) {
            $recaptchaOldSettings = [
                'id' => 1,
                'recaptcha_login' => 0,
                'recaptcha_signup' => 0,
                'recaptcha_abit_access' => 0,
            ];
        }

        $this->truncateTable('recaptcha');
        $this->dropColumn('recaptcha', 'recaptcha_login');
        $this->dropColumn('recaptcha', 'recaptcha_signup');
        $this->dropColumn('recaptcha', 'recaptcha_abit_access');

        Yii::$app->db->schema->refresh();

        $this->addColumn('recaptcha', 'name', $this->string()->defaultValue(''));
        $this->addColumn('recaptcha', 'description', $this->string(50)->defaultValue(''));
        $this->addColumn('recaptcha', 'version', $this->integer()->defaultValue(1));

        Yii::$app->db->schema->refresh();

        $insertRows = [
            'recaptcha_login' => 'Страница "входа в личный кабинет"',
            'recaptcha_signup' => 'Страница "регистрации"',
            'recaptcha_abit_access' => 'Страница "создания пароля"',
        ];
        foreach ($insertRows as $I => $description) {
            $name = str_replace('recaptcha_', '', $I);
            $version = $recaptchaOldSettings[$I] == 0 ? 1 : 2;

            $this->insert(
                'recaptcha',
                [
                    'name' => $name,
                    'version' => $version,
                    'description' => $description,
                ]
            );
        }
    }

    


    public function safeDown()
    {
        $this->truncateTable('recaptcha');
        $this->dropColumn('recaptcha', 'name');
        $this->dropColumn('recaptcha', 'version');
        $this->dropColumn('recaptcha', 'description');

        Yii::$app->db->schema->refresh();

        $this->addColumn('recaptcha', 'recaptcha_login', $this->boolean()->defaultValue(false));
        $this->addColumn('recaptcha', 'recaptcha_signup', $this->boolean()->defaultValue(false));
        $this->addColumn('recaptcha', 'recaptcha_abit_access', $this->boolean()->defaultValue(false));

        Yii::$app->db->schema->refresh();

        $this->insert(
            'recaptcha',
            [
                'recaptcha_login' => false,
                'recaptcha_signup' => false,
                'recaptcha_abit_access' => false,
            ]
        );
    }
}
