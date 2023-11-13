<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m230307_093142_set_defualt_icons_for_doc_status extends MigrationWithDefaultOptions
{
    private const TN = '{{%document_check_status_reference_type}}';
    private const DATA = [
        'Не проверен' => [
            'icon_class' => 'fa fa-clock-o',
            'icon_color' => 'text-secondary',
        ],
        'На проверке' => [
            'icon_class' => 'fa fa-eye',
            'icon_color' => 'text-info',
        ],
        'Не прошел проверку' => [
            'icon_class' => 'fa fa-exclamation-triangle',
            'icon_color' => 'text-danger',
        ],
        'Проверен' => [
            'icon_class' => 'fa fa-check',
            'icon_color' => 'text-success',
        ],
    ];

    


    public function safeUp()
    {
        foreach (self::DATA as $reference_name => $columns) {
            $docStatusId = (new Query())
                ->select('id')
                ->from(self::TN)
                ->where(['reference_name' => $reference_name])
                ->scalar();

            if ($docStatusId) {
                $this->update(
                    self::TN,
                    array_merge($columns, ['updated_at' => time()]),
                    ['id' => $docStatusId]
                );
            } else {
                $this->insert(
                    self::TN,
                    array_merge($columns, [
                        'reference_name' => $reference_name,
                        'reference_class_name' => 'Перечисление.СтатусыПроверкиДокументовПоступающих',
                        'updated_at' => time(),
                        'created_at' => time(),
                    ])
                );
            }
        }
    }

    


    public function safeDown()
    {
    }
}
