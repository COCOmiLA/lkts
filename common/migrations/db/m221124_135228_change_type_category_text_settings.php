<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221124_135228_change_type_category_text_settings extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';

    private const TRANSLATE_DICTIONARY = [
        'CATEGORY_ALL' => [
            'old' => '0',
            'new' => 'all',
        ],
        'CATEGORY_INDEX' => [
            'old' => '1',
            'new' => 'index',
        ],
        'CATEGORY_QUESTIONARY' => [
            'old' => '2',
            'new' => 'questionary',
        ],
        'CATEGORY_EXAMS' => [
            'old' => '3',
            'new' => 'exams',
        ],
        'CATEGORY_APPLICATION' => [
            'old' => '4',
            'new' => 'application',
        ],
        'CATEGORY_EDUCATION' => [
            'old' => '5',
            'new' => 'education',
        ],
        'CATEGORY_DORMITORY' => [
            'old' => '6',
            'new' => 'dormitory',
        ],
        'CATEGORY_SANDBOX' => [
            'old' => '7',
            'new' => 'sandbox',
        ],
        'CATEGORY_INDACH' => [
            'old' => '8',
            'new' => 'individual_achievements',
        ],
        'CATEGORY_BENEFITS' => [
            'old' => '9',
            'new' => 'benefits',
        ],
        'CATEGORY_SCANS' => [
            'old' => '10',
            'new' => 'scans',
        ],
        'CATEGORY_STATUSES' => [
            'old' => '11',
            'new' => 'statuses',
        ],
        'CATEGORY_TOOLTIPS' => [
            'old' => '12',
            'new' => 'tooltips',
        ],
        'CATEGORY_ALL_APPLICATIONS' => [
            'old' => '13',
            'new' => 'all_applications',
        ],
        'CATEGORY_NOTIFICATIONS' => [
            'old' => '14',
            'new' => 'notifications',
        ],
    ];

    


    public function safeUp()
    {
        $this->alterColumn(self::TN, 'category', $this->string(50)->defaultValue('all'));
        $this->db->schema->refresh();

        foreach (self::TRANSLATE_DICTIONARY as ['old' => $oldValue, 'new' => $newValue]) {
            $this->update(
                self::TN,
                ['category' => $newValue],
                ['category' => $oldValue]
            );
        }
    }

    


    public function safeDown()
    {
        foreach (self::TRANSLATE_DICTIONARY as ['old' => $oldValue, 'new' => $newValue]) {
            $this->update(
                self::TN,
                ['category' => $oldValue],
                ['category' => $newValue]
            );
        }

        $this->alterColumn(self::TN, 'category', $this->integer()->defaultValue(0));
        $this->db->schema->refresh();
    }
}
