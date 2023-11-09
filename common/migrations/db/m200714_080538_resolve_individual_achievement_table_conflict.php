<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\DocumentType;
use common\models\IndividualAchievementDocumentType;
use common\modules\abiturient\models\IndividualAchievementNoCollection;
use yii\helpers\ArrayHelper;




class m200714_080538_resolve_individual_achievement_table_conflict extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        Yii::$app->db->schema->refresh();
        $achivements = IndividualAchievementNoCollection::find()->where(['document_type_id' => null])->all();
        foreach ($achivements as $achivement) {
            if (isset($achivement->document_type)) {

                $type = DocumentType::findOne([
                    'description' => $achivement->document_type,
                    'archive' => false
                ]);
                if ($type != null) {
                    $avali = IndividualAchievementDocumentType::findOne([
                        'campaign_code' => ArrayHelper::getValue($achivement, 'achievementType.campaign_code'),
                        'document_type' => $type->code
                    ]);
                    if ($avali == null) {
                        $avali = new IndividualAchievementDocumentType();
                    }
                    $avali->campaign_code = ArrayHelper::getValue($achivement, 'achievementType.campaign_code');
                    $avali->document_description = $type->description;
                    $avali->document_type = $type->code;
                    $avali->scan_required = false;
                    $avali->archive = true;
                    $avali->from1c = true;
                    $array = [
                        'campaign_code',
                        'document_description',
                        'document_type',
                        'scan_required',
                        'archive',
                        'from1c',
                    ];
                    try {
                        if ($avali->validate($array)) {
                            $avali->save(false, $array);
                        } else {
                            Yii::error("Ошибка при выполнении миграции для таблицы 'individual_achievements'\n" . $avali->errors);
                            return false;
                        }
                        $this->update(
                            'individual_achievement',
                            ['document_type_id' => $avali->id],
                            ['id' => $achivement->id]
                        );
                    } catch (\Throwable $e) {
                        Yii::error("Ошибка при выполнении миграции для таблицы 'individual_achievements'\n" . $e->getMessage());
                        return false;
                    }
                }
            }
        }
    }

    


    public function safeDown()
    {
    }
}
