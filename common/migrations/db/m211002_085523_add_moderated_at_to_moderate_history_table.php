<?php

use common\modules\abiturient\models\bachelor\ModerateHistory;
use yii\db\Migration;




class m211002_085523_add_moderated_at_to_moderate_history_table extends Migration
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_moderate_history}}', 'moderated_at', $this->integer());
        Yii::$app->db->schema->refresh();

        foreach (ModerateHistory::find()->all() as $item) {
            $item->moderated_at = $item->updated_at;
            $item->save(false);
        }
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_moderate_history}}', 'moderated_at');
        Yii::$app->db->schema->refresh();
    }

}
