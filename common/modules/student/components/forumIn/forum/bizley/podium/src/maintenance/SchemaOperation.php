<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\maintenance;

use common\components\migrations\traits\TableOptionsTrait;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Exception;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Html;














class SchemaOperation extends Component
{
    const TYPE_SUCCESS = 0;
    const TYPE_WARNING = 1;
    const TYPE_ERROR = 2;

    


    public $db;

    


    public $type = self::TYPE_ERROR;

    


    public $tableOptions;

    


    protected $_table;

    


    protected $_prefix = 'podium_';

    


    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->module->db, Connection::class);
        $this->tableOptions = TableOptionsTrait::GetTableOptions();
    }

    





    protected function addColumn($col, $type)
    {
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($type)) {
            return Yii::t('podium/flash', 'Installation aborted! Column type missing.');
        }
        try {
            $this->db->createCommand()->addColumn($this->table, $col, $type)->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Table column {name} has been added', ['name' => $col]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table column {name} adding', ['name' => $col])
            );
        }
    }

    









    protected function addForeign($key, $ref, $col, $delete = null, $update = null)
    {
        if (empty($key)) {
            return Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.');
        }
        if (empty($ref)) {
            return Yii::t('podium/flash', 'Installation aborted! Foreign key reference missing.');
        }
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Referenced columns missing.');
        }
        try {
            $this->db->createCommand()->addForeignKey(
                $this->getForeignName($key), $this->table, $key,
                $this->getTableName($ref), $col, $delete, $update
            )->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Table foreign key {name} has been added', [
                'name' => $this->getForeignName($key)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table foreign key {name} adding', [
                    'name' => $this->getForeignName($key)
                ])
            );
        }
    }

    






    protected function addIndex($name, $cols)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! Index name missing.');
        }
        if (empty($cols)) {
            return Yii::t('podium/flash', 'Installation aborted! Index columns missing.');
        }
        try {
            $this->db->createCommand()->createIndex($this->getIndexName($name), $this->table, $cols)->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Table index {name} has been added', [
                'name' => $this->getIndexName($name)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table index {name} adding', [
                    'name' => $this->getIndexName($name)
                ])
            );
        }
    }

    





    protected function alterColumn($col, $type)
    {
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($type)) {
            return Yii::t('podium/flash', 'Installation aborted! Column type missing.');
        }
        try {
            $this->db->createCommand()->alterColumn($this->table, $col, $type)->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Table column {name} has been updated', ['name' => $col]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table column {name} updating', ['name' => $col])
            );
        }
    }

    





    protected function createTable($schema)
    {
        if (empty($schema)) {
            return Yii::t('podium/flash', 'Installation aborted! Database schema missing.');
        }
        try {
            $this->db->createCommand()->createTable($this->table, $schema, $this->tableOptions)->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Table {name} has been created', ['name' => $this->rawTable]));
        } catch (Exception $e) {
            if ($this->_table != 'log') {
                
                Yii::error($e->getMessage(), __METHOD__);
            }
            return Yii::t('podium/flash', 'Error during table {name} creating', [
                    'name' => $this->rawTable
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    



    protected function dropTable()
    {
        try {
            if ($this->db->schema->getTableSchema($this->table, true) !== null) {
                $this->db->createCommand()->dropTable($this->table)->execute();
                return $this->returnWarning(Yii::t('podium/flash', 'Table {name} has been dropped', ['name' => $this->rawTable]));
            }
            return true;
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table {name} dropping', [
                    'name' => $this->rawTable
                ])
            );
        }
    }

    




    protected function dropColumn($col)
    {
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        try {
            $this->db->createCommand()->dropColumn($this->table, $col)->execute();
            return $this->returnWarning(Yii::t('podium/flash', 'Table column {name} has been dropped', ['name' => $col]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table column {name} dropping', ['name' => $col])
            );
        }
    }

    




    protected function dropForeign($name)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.');
        }
        try {
            $this->db->createCommand()->dropForeignKey($this->getForeignName($name), $this->table)->execute();
            return $this->returnWarning(Yii::t('podium/flash', 'Table foreign key {name} has been dropped', [
                'name' => $this->getForeignName($name)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table foreign key {name} dropping', [
                    'name' => $this->getForeignName($name)
                ])
            );
        }
    }

    




    protected function dropIndex($name)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! Index name missing.');
        }
        try {
            $this->db->createCommand()->dropIndex($this->getIndexName($name), $this->table)->execute();
            return $this->returnWarning(Yii::t('podium/flash', 'Table index {name} has been dropped', [
                'name' => $this->getIndexName($name)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table index {name} dropping', [
                    'name' => $this->getIndexName($name)
                ])
            );
        }
    }

    




    protected function rename($name)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! New table name missing.');
        }
        try {
            $this->db->createCommand()->renameTable($this->table, $this->getTableName($name))->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Table {name} has been renamed to {new}', [
                'name' => $this->rawTable,
                'new' => $this->getTableName($name)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table {name} renaming to {new}', [
                    'name' => $this->rawTable,
                    'new' => $this->getTableName($name)
                ])
            );
        }
    }

    





    protected function renameColumn($col, $name)
    {
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! New column name missing.');
        }
        try {
            $this->db->createCommand()->renameColumn($this->table, $col, $name)->execute();
            return $this->returnSuccess(Yii::t('podium/flash', 'Table column {name} has been renamed to {new}', [
                'name' => $col,
                'new' => $name
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table column {name} renaming to {new}', [
                    'name' => $col,
                    'new' => $name
                ])
            );
        }
    }

    




    public function getForeignName($name)
    {
        return 'fk-' . $this->rawTable . '-' . (is_array($name) ? implode('_', $name) : $name);
    }

    




    public function getIndexName($name)
    {
        return 'idx-' . $this->rawTable . '-' . $name;
    }

    



    public function getModule()
    {
        return Podium::getInstance();
    }

    




    public function getRawTable()
    {
        return $this->_prefix . $this->_table;
    }

    



    public function getTable()
    {
        return $this->_table == '...' ? '...' : $this->getTableName($this->_table);
    }

    




    public function getTableName($name)
    {
        return '{{%' . $this->_prefix . $name . '}}';
    }

    



    public function setTable($value)
    {
        $this->_table = $value;
    }

    





    public function returnSuccess($message)
    {
        $this->type = self::TYPE_SUCCESS;
        return $message;
    }

    





    public function returnWarning($message)
    {
        $this->type = self::TYPE_WARNING;
        return $message;
    }

    







    public function returnError($exception, $method, $message)
    {
        Yii::error($exception, $method);
        return $message . ':' . Html::tag('pre', $exception);
    }
}
