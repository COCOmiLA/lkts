<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\UserRegulation;
use yii\helpers\ArrayHelper;




class m210730_132202_resolve_several_regulation_data extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $forbiddenRegulationsWithNoApplicationAndAttachment = UserRegulation::find()
            ->from('user_regulation ur1')
            ->where(['exists', UserRegulation::find()
                ->from('user_regulation ur2')
                ->andWhere('ur1.regulation_id = ur2.regulation_id')
                ->andWhere('ur1.owner_id = ur2.owner_id')
                ->andWhere('ur1.is_confirmed = ur2.is_confirmed')
                ->andWhere('ur1.id != ur2.id')
            ])
            ->andWhere([
                'ur1.application_id' => null,
                'ur1.attachment_id' => null
            ]);
        $forbiddenRegulationsAll = $forbiddenRegulationsWithNoApplicationAndAttachment->all();
        $forbiddenRegulationsAll = ArrayHelper::index($forbiddenRegulationsAll, null, ['regulation_id', 'owner_id']);
        foreach ($forbiddenRegulationsAll as $regulationIds => $owners) {
            foreach ($owners ?? [] as $ownerId => $userRegulations) {
                $count = count($userRegulations);
                if($count > 1) {
                    $i = 0;
                    while ($count > 1 && $i < $count) {
                        $userRegulation = $userRegulations[$i]->delete();
                        $count--;
                        $i++;
                    }
                }
            }
        }
    }

    


    public function safeDown()
    {
        return true;
    }

    













}
