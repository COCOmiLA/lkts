<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\ContractorManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\Contractor;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\models\errors\RecordNotValid;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\PassportData;
use League\Glide\Api\Manipulator\Contrast;
use yii\db\Query;
use yii\helpers\VarDumper;




class m221124_230439_restore_contractor_ids extends MigrationWithDefaultOptions
{
    protected static $batch_size = 5000;

    const CONTRACTOR_STATUS_PENDING = 'pending';

    


    public function safeUp()
    {
        $this->restoreContractors('passport_data', 'id', 'contractor_id', 'issued_by', 'department_code');
        $this->restoreContractors('education_data', 'id', 'contractor_id', 'school_name');
        $this->restoreContractors('bachelor_preferences', 'id', 'contractor_id', 'document_organization');
        $this->restoreContractors('bachelor_target_reception', 'id', 'target_contractor_id', 'name_company');
        $this->restoreContractors('bachelor_target_reception', 'id', 'document_contractor_id', 'document_organization');
        $this->restoreContractors('individual_achievement', 'id', 'contractor_id', 'document_giver');
    }

    public function restoreContractors($table_name, $pk_column, $contractor_link_column, $name_column, $subdivision_code_column = null)
    {
        $db = $this->db;
        $query = (new Query())
            ->from("{{%$table_name}}")
            ->where([$contractor_link_column => null]);

        foreach ($query->each(static::$batch_size) as $row) {
            try {
                if (empty($row[$name_column])) {
                    continue;
                }

                $this->insert('{{%dictionary_contractor}}', [
                    'name' => $row[$name_column],
                    'subdivision_code' => $row[$subdivision_code_column] ?? '',
                    'status' => static::CONTRACTOR_STATUS_PENDING,
                    'archive' => false
                ]);

                $contractor_id = $db->getLastInsertID($db->driverName == 'pgsql' ? 'dictionary_contractor_id_seq' : null);
                if ($contractor_id) {
                    $this->update("{{%$table_name}}", [
                        $contractor_link_column => $contractor_id
                    ], [
                        'id' => $row[$pk_column]
                    ]);
                }
            } catch (\Throwable $e) {
                \Yii::error($e->getMessage(), 'CONTRACTOR_RESTORE');
            }
        }
    }

    


    public function safeDown()
    {
        return true;
    }
}
