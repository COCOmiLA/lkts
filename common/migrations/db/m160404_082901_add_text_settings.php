<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160404_082901_add_text_settings extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%text_settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->string(500)->notNull(),
            'value' => $this->string(3000)->notNull(),
            'order'=>$this->integer()->notNull()->defaultValue(0),
            
        ], $tableOptions);
              
        $this->insert('{{%text_settings}}', [
            'name' => 'indexbutton_hint',
            'description' => 'Сноска под кнопкой Заполнить анкету/подать заявление',
            'value' => 'Внимание! Прием электронных заявлений ведется только на общих основаниях. Если вы льготник – обратитесь в приемную комиссию лично.',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'applist_hint',
            'description' => 'Пункт в списке на главной',
            'value' => 'Заполните анкету',
            'order' => 1,
        ]);
                
        $this->insert('{{%text_settings}}', [
            'name' => 'applist_hint',
            'description' => 'Пункт в списке на главной',
            'value' => 'Выберите направления для поступления (максимум 3)',
            'order' => 2,
        ]);
                        
        $this->insert('{{%text_settings}}', [
            'name' => 'applist_hint',
            'description' => 'Пункт в списке на главной',
            'value' => 'Когда заявление проверят и примут или отклонят, вы получите уведомление по электронной почте',
            'order' => 3,
        ]);
                                
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_blocked',
            'description' => 'Сообщение пользователю о том, что анкета на проверке у модератора',
            'value' => 'Анкета находится на проверке у модератора',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_sended',
            'description' => 'Сообщение о том, что анкета сохранена',
            'value' => 'Анкета сохранена на портале',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_approved_sandbox_on',
            'description' => 'Сообщение пользователю о том, что анкета проверена модератором и подана в 1С',
            'value' => 'Анкета проверена модератором и подана в приемную комиссию',
        ]);
                
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_approved_sandbox_off',
            'description' => 'Сообщение пользователю о том, что анкеты подана в 1С (песочница выключена)',
            'value' => 'Анкета подана в приемную комиссию',
        ]);
                        
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_notapproved',
            'description' => 'Сообщение пользователю о том, что анкета отклонена модератором',
            'value' => 'Анкета была отклонена модератором',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_rejected_by1c',
            'description' => 'Сообщение пользователю о том, что анкета отклонена 1С',
            'value' => 'Анкета была отклонена 1С',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_save_success',
            'description' => 'Сообщение пользователю об успешном сохранении анкеты',
            'value' => 'Сохранение прошло успешно.',
        ]);
                
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary_file_error',
            'description' => 'Сообщение пользователю о неприкреплении обязательных для прикрепления файлов',
            'value' => 'Внимание! Вы не прикрепили обязательные копии документов:',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'ege_blocked',
            'description' => 'Сообщение пользователю о том, что ЕГЭ на проверке у модератора',
            'value' => 'Результаты ЕГЭ находятся на проверке у модератора',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'ege_sended',
            'description' => 'Сообщение о том, что результаты ЕГЭ сохранены',
            'value' => 'Результаты ЕГЭ отправлены и ожидают проверки модератором',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'ege_approved_sandbox_on',
            'description' => 'Сообщение пользователю о том, что результаты ЕГЭ проверены модератором и поданы в 1С',
            'value' => 'Результаты ЕГЭ одобрены модератором и поданы в приемную комиссию',
        ]);
                
        $this->insert('{{%text_settings}}', [
            'name' => 'ege_approved_sandbox_off',
            'description' => 'Сообщение пользователю о том, что результаты ЕГЭ поданы в 1С (песочница выключена)',
            'value' => 'Результаты ЕГЭ поданы в приемную комиссию',
        ]);
                        
        $this->insert('{{%text_settings}}', [
            'name' => 'ege_notapproved',
            'description' => 'Сообщение пользователю о том, что результаты ЕГЭ отклонены модератором',
            'value' => 'Результаты ЕГЭ были отклонены модератором',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'ege_rejected_by1c',
            'description' => 'Сообщение пользователю о том, что результаты ЕГЭ отклонены 1С',
            'value' => 'Результаты ЕГЭ были отклонены 1С',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'save_error',
            'description' => 'Сообщение пользователю об ошибке сохранения',
            'value' => 'Возникла ошибка сохранения. Попробуйте повторить позднее.',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'moder_comment',
            'description' => 'Текст перед сообщением от модератора',
            'value' => 'Комментарий проверяющего:',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'load_from_1c_info',
            'description' => 'Текст о том, что обновление из 1С возможно только после подачи заявления',
            'value' => 'Получение информации из 1С возможно после подачи заявления',
        ]);
        
        
        $this->insert('{{%text_settings}}', [
            'name' => 'application_blocked',
            'description' => 'Сообщение пользователю о том, что заявление на проверке у модератора',
            'value' => 'Заявление находится на проверке у модератора',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'application_sended',
            'description' => 'Сообщение о том, что заявление сохранено',
            'value' => 'Заявление отправлено и ожидает проверки модератором',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'application_approved_sandbox_on',
            'description' => 'Сообщение пользователю о том, что заявление проверено модератором и подано в 1С',
            'value' => 'Заявление одобрено модератором и подано в приемную комиссию',
        ]);
                
        $this->insert('{{%text_settings}}', [
            'name' => 'application_approved_sandbox_off',
            'description' => 'Сообщение пользователю о том, что заявление подано в 1С (песочница выключена)',
            'value' => 'Заявление подано в приемную комиссию',
        ]);
                        
        $this->insert('{{%text_settings}}', [
            'name' => 'application_notapproved',
            'description' => 'Сообщение пользователю о том, что заявление отклонено модератором',
            'value' => 'Заявление было отклонено модератором',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'application_rejected_by1c',
            'description' => 'Сообщение пользователю о том, что заявление отклонено 1С',
            'value' => 'Заявление было отклонено 1С',
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'sandbox_1c_error',
            'description' => 'Сообщение модератору о том, что возникла ошибка при сохранении в 1С',
            'value' => 'Возникла ошибка сохранения в 1С. Попробуйте повторить позднее',
        ]);
                
        $this->insert('{{%text_settings}}', [
            'name' => 'sandbox_modified',
            'description' => 'Сообщение модератору о том, что заявление было изменено пользователем',
            'value' => 'Внимание! Заявление могло быть изменено пользователем. Пожалуйста, обновите страницу перед проверкой',
        ]);
        
        
        Yii::$app->db->schema->refresh();       
    }

    public function safeDown()
    {
        $this->dropTable('{{%text_settings}}');
        Yii::$app->db->schema->refresh();
    }
}
