<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\Regulation;
use common\modules\abiturient\models\File;
use yii\helpers\FileHelper;




class m220505_142850_remove_content_file_props extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        [$single, $multiple, $table] = ['regulation', 'regulations', '{{%regulation}}'];

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

        Yii::$app->db->schema->refresh();

        $regulations = Regulation::find()->andWhere(['content_type' => Regulation::CONTENT_TYPE_FILE])->all();
        foreach ($regulations as $instance) {
            $instance_attributes = $instance->attributes;
            $old_base_path = FileHelper::normalizePath(Yii::getAlias(Regulation::FILE_PATH));
            if (file_exists($old_base_path . DIRECTORY_SEPARATOR . $instance_attributes['content_file'])) {
                $file = new File();
                $file->base_path = $instance->getPathToStoreFiles();
                $file->real_file_name = $instance_attributes['content_file'];
                $file->upload_name = $instance_attributes['content_file'];

                $toName = $file->getFilePath();
                if (!is_dir(dirname($toName))) {
                    mkdir(dirname($toName), 0777, true);
                }
                if (rename($old_base_path . DIRECTORY_SEPARATOR . $instance_attributes['content_file'], $toName)) {
                    $file->calcFileHash();
                    $file->save(false);
                    $instance->link('linkedFile', $file);
                }
            }
        }

        $this->dropColumn(Regulation::tableName(), 'content_file');
        $this->dropColumn(Regulation::tableName(), 'content_file_extension');
    }

    


    public function down()
    {
        [$single, $multiple, $table] = ['regulation', 'regulations', '{{%regulation}}'];

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

        $this->addColumn(Regulation::tableName(), 'content_file', $this->string());
        $this->addColumn(Regulation::tableName(), 'content_file_extension', $this->string());
    }
}
