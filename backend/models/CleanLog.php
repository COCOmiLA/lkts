<?php

namespace backend\models;

use Yii;
use yii\base\Model;















class CleanLog extends Model
{
    
    private $_count;

    
    public $className = '';

    
    public $numberToDelete = '0';

    public function rules()
    {
        return [
            [
                ['className'],
                'string'
            ],
            [
                ['numberToDelete'],
                'number',
                'max' => $this->count,
            ],
            [
                ['numberToDelete'],
                'required'
            ]
        ];
    }

    


    public function attributeLabels()
    {
        return ['numberToDelete' => Yii::t(
            'backend',
            'Укажите количество записей на удаление (максимально {count}) из "{tableName}"',
            [
                'count' => $this->count,
                'tableName' => $this->tableName
            ]
        ),];
    }

    


    public function getCount(): string
    {
        if (empty($this->className)) {
            return (string) intval(PHP_INT_MAX);
        }

        if (!$this->_count) {
            $this->_count = $this->className::find()
                ->count();
        }
        return $this->_count;
    }

    


    public function getNumbersToDeleteList(): array
    {
        return [$this->count => Yii::t('backend', 'Все'),];
    }

    


    public function getIndex(): string
    {
        return md5($this->className);
    }

    


    public function getTableName(): string
    {
        $tableAliases = [
            SystemLog::class => Yii::t('backend', 'Журнала ошибок'),
            SystemLogInfo::class => Yii::t('backend', 'Информационного журнала'),
        ];

        if (key_exists($this->className, $tableAliases)) {
            return $tableAliases[$this->className];
        }

        return str_replace(
            '%',
            '',
            str_replace(
                '}',
                '',
                str_replace(
                    '{',
                    '',
                    $this->className::tableName()
                )
            )
        );
    }

    public function getClientFormName()
    {
        return mb_strtolower($this->formName());
    }
}
