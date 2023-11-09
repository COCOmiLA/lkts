<?php

use common\components\EnvironmentManager\EnvironmentManager;
use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m210720_151102_resolve_old_version extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $versions = (new Query)
            ->select('version')
            ->from(EnvironmentManager::$portalDatabaseVersionTable)
            ->all();

        foreach ($versions as $version) {
            $actualVersion = $version['version'];
            $exploded = explode('.', $actualVersion);
            Yii::$app->db
                ->createCommand("UPDATE portal_database_version set subversion1 = :subversion1, subversion2 = :subversion2, subversion3 = :subversion3, subversion4 = :subversion4 where version = :version")
                ->bindValue(':subversion1', $exploded[0])
                ->bindValue(':subversion2', $exploded[1])
                ->bindValue(':subversion3', $exploded[2])
                ->bindValue(':subversion4', $exploded[3])
                ->bindValue(':version', $actualVersion)
                ->execute();
        }

    }

    


    public function safeDown()
    {
        return;
    }

    













}
