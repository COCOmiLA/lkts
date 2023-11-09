<?php

use common\components\LikeQueryManager;
use yii\db\Migration;
use yii\db\Query;




class m211117_075508_filter_setting_label_convertor extends Migration
{
    


    public function safeUp()
    {
        Yii::$app->db->schema->refresh();

        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        

        $oldFormattedLabelsQuery = (new Query)
            ->select([
                'id',
                'CONCAT(
                    \'Подпись колонки для поля "\',
                    name,
                    \'" в таблице фильтров: `\',
                    label,
                    \'`\'
                ) as label2'
            ])
            ->from('filters_setting')
            ->where(['NOT LIKE', 'label', '`'])
            ->all();

        if (!empty($oldFormattedLabelsQuery)) {
            foreach ($oldFormattedLabelsQuery as $old) {
                $this->update(
                    'filters_setting',
                    ['label' => $old['label2']],
                    ['id' => $old['id']]
                );
            }
        }

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        Yii::$app->db->schema->refresh();

        $newFormattedLabelsQuery = (new Query)
            ->select(['id', 'label'])
            ->from('filters_setting')
            ->where([LikeQueryManager::getActionName(), 'label', '`'])
            ->all();

        if (!empty($newFormattedLabelsQuery)) {
            foreach ($newFormattedLabelsQuery as $new) {
                if (preg_match('/`([^`]+)`/', $new['label'], $matches)) {
                    $this->update(
                        'filters_setting',
                        ['label' => $matches[1]],
                        ['id' => $new['id']]
                    );
                }
            }
        }

        Yii::$app->db->schema->refresh();
    }
}
