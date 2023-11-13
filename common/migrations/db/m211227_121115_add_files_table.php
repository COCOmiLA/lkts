<?php

use backend\models\Consent;
use backend\models\DocumentTemplate;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\Attachment;
use common\models\AttachmentArchive;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\models\File;
use yii\db\Query;




class m211227_121115_add_files_table extends MigrationWithDefaultOptions
{
    


    public $file_containers = [
        ['attachment',          'attachments',          '{{%attachment}}'],
        ['consent',             'consents',             '{{%consent}}'],
        ['document_template',   'document_templates',   '{{%document_template}}'],
        ['archived_attachment', 'archived_attachments', '{{%attachment_archive}}'],
        ['agreement',           'agreements',           '{{%admission_agreement}}'],
        ['agreement_decline',   'agreement_declines',   '{{%agreement_decline}}'],
    ];

    


    public function safeUp()
    {
        $tableName = '{{%files}}';
        if ($this->db->getTableSchema($tableName, true) === null) {
            $this->createTable('{{%files}}', [
                'id' => $this->primaryKey(),
                'content_hash' => $this->string(),
                'real_file_name' => $this->string(),
                'upload_name' => $this->string(),
                'uid' => $this->string(),
                'base_path' => $this->string()->notNull(),
                'updated_at' => $this->integer(),
                'created_at' => $this->integer(),
            ]);
        }

        foreach ($this->file_containers as [$single, $multiple, $table]) {
            $tableName = "{{%{$multiple}_files}}";
            if ($this->db->getTableSchema($tableName, true) === null) {
                $this->createTable(
                    $tableName,
                    [
                        'id' => $this->primaryKey(),
                        "{$single}_id" => $this->integer(),
                        'file_id' => $this->integer(),
                    ]
                );

                $this->createIndex("{{%idx-{$multiple}_files-{$single}_id}}", $tableName, "{$single}_id");
                $this->createIndex("{{%idx-{$multiple}_files-file_id}}", $tableName, 'file_id');

                $this->addForeignKey(
                    "fk-{$multiple}_files-{$single}_id",
                    $tableName,
                    "{$single}_id",
                    $table,
                    "id",
                );
                $this->addForeignKey(
                    "fk-{$multiple}_files-file_id",
                    $tableName,
                    "file_id",
                    "{{%files}}",
                    "id",
                );
            }
        }
        Yii::$app->db->schema->refresh();

        $modelList = [
            [Attachment::class, '@storage/web/scans/'],
            [Consent::class, '@storage/web/uni/'],
            [DocumentTemplate::class, '@storage/web/template/'],
            [AttachmentArchive::class, '@storage/web/scans/'],
            [AdmissionAgreement::class, '@storage/web/aa-scans/'],
            [AgreementDecline::class, '@storage/web/aa-scans-decline/'],
            [BachelorPreferences::class, null],
            [BachelorTargetReception::class, null],
            [AdmissionAgreement::class, null],
            [AgreementDecline::class, null],
        ];
        foreach ($modelList as [$model, $base_path]) {
            
            if ($base_path) {
                foreach ((new Query())->from($model::tableName())->all() as $item) {
                    try {
                        if ($item['filename']) {
                            $instance = $model::findOne($item['id']);
                            $file = new File();
                            $file->base_path = $base_path . $instance->getHash();
                            $file->real_file_name = $item['filename'];
                            $file->upload_name = $item['file'];
                            $file->calcFileHash();
                            $file->save(false);
                            $instance->link('linkedFile', $file);
                        }
                    } catch (Throwable $e) {
                        echo $e->getMessage() . PHP_EOL;
                    }
                }
            }

            if (Yii::$app->db->schema->getTableSchema($model::tableName())->getColumn('file')) {
                $this->dropColumn($model::tableName(), 'file');
            }
            if (Yii::$app->db->schema->getTableSchema($model::tableName())->getColumn('filename')) {
                $this->dropColumn($model::tableName(), 'filename');
            }
        }
    }

    


    public function safeDown()
    {
        foreach ([
            Attachment::class,
            Consent::class,
            DocumentTemplate::class,
            AttachmentArchive::class,
            AdmissionAgreement::class,
            AgreementDecline::class,
            BachelorPreferences::class,
            BachelorTargetReception::class,
            AdmissionAgreement::class,
            AgreementDecline::class,
        ] as $model) {

            $this->addColumn($model::tableName(), 'filename', $this->string());
            $this->addColumn($model::tableName(), 'file', $this->string());
        }

        foreach ($this->file_containers as [$single, $multiple, $table]) {
            $this->dropForeignKey(
                "fk-{$multiple}_files-{$single}_id",
                "{{%{$multiple}_files}}",
            );
            $this->dropForeignKey(
                "fk-{$multiple}_files-file_id",
                "{{%{$multiple}_files}}",
            );

            $this->dropIndex("{{%idx-{$multiple}_files-{$single}_id}}", "{{%{$multiple}_files}}");
            $this->dropIndex("{{%idx-{$multiple}_files-file_id}}", "{{%{$multiple}_files}}");

            $this->dropTable("{{%{$multiple}_files}}",);
        }
        $this->dropTable('{{%files}}');
    }
}
