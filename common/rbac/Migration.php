<?php

namespace common\rbac;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Migration as DbMigration;
use yii\db\MigrationInterface;
use yii\rbac\BaseManager;




class Migration extends DbMigration implements MigrationInterface
{
    


    public $auth = 'authManager';

    


    public function init()
    {
        parent::init();
        $this->auth = Yii::$app->get('authManager');
    }
}
