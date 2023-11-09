<?php

use yii\db\Migration;
use yii\db\Query;
use yii\helpers\ArrayHelper;




class m211026_105737_check_code_settings_doubles extends Migration
{
    


    public function safeUp()
    {
        $doubles = (new Query)
            ->select(['name'])
            ->from('code_settings')
            ->groupBy(['name'])
            ->having(['>', 'COUNT(name)', 1])
            ->all();

        if (empty($doubles)) {
            return true;
        }

        $doubles = ArrayHelper::getColumn($doubles, 'name');
        $idToDelete = [];
        foreach ($doubles as $name) {
            $toDelete = (new Query)
                ->select(['id'])
                ->from('code_settings')
                ->where(['name' => $name])
                ->orderBy('id')
                ->one();

            $idToDelete[] = $toDelete['id'];
        }

        $this->delete(
            'code_settings',
            ['IN', 'id', $idToDelete]
        );

        return true;
    }

    


    public function safeDown()
    {
    }
}
