<?php

use common\components\dictionaryManager\GetReferencesManager\GetReferencesManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;




class m220615_070359_restore_corrupted_user_refs extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        
        $possible_broken_refs_batches = StoredUserReferenceType::find()->where(['>', 'created_at', 1654387245])->batch();
        foreach ($possible_broken_refs_batches as $possible_broken_refs) {
            try {
                $refs_to_search = array_map(function (StoredUserReferenceType $ref) {
                    return [
                        'ValueType' => 'Справочник.ФизическиеЛица',
                        'ValueRef' => ReferenceTypeManager::GetReference($ref),
                    ];
                }, $possible_broken_refs);

                $result = GetReferencesManager::getReferences(StoredUserReferenceType::getReferenceClassToFill(), '', GetReferencesManager::FILTER_TYPE_AP, [
                    'SimpleFilters' => [
                        'Field' => 'Ссылка',
                        'Comparison' => 'InList',
                        'Values' => $refs_to_search,
                    ]
                ]);
                $references_generator = $result->getReferences();
                $found_references = [];
                foreach ($references_generator as $raw_reference) {
                    $found_references[] = ReferenceTypeManager::GetOrCreateReference(StoredUserReferenceType::class, $raw_reference);
                }

                $broken_ref_ids = array_diff(
                    \yii\helpers\ArrayHelper::getColumn($possible_broken_refs, 'id'),
                    \yii\helpers\ArrayHelper::getColumn($found_references, 'id'),
                );
                foreach ($broken_ref_ids as $broken_ref_id) {
                    $broken_ref = StoredUserReferenceType::findOne($broken_ref_id);
                    $this->restoreBrokenRef($broken_ref);
                }
            } catch (Throwable $e) {
                Yii::error("Ошибка при восстановлении битых ссылок на ФизическиеЛица: {$e->getMessage()}");
                echo $e->getMessage();
            }
        }
    }

    


    public function down()
    {

    }

    private function restoreBrokenRef(StoredUserReferenceType $userRef)
    {
        $users = \common\models\User::find()->where(['user_ref_id' => $userRef->id])->all();
        if ($users) {
            foreach ($users as $user) {
                $user->guid = null;
                $user->user_ref_id = null;
                $user->save(false);
            }
            $userRef->delete();
        }
    }
}
