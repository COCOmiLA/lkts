<?php

namespace common\models\dictionary;

use common\components\LikeQueryManager;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;




class FiasDoma extends \yii\db\ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%dictionary_fias_doma}}';
    }

    


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false
            ]
        ];
    }

    


    public function rules()
    {
        return [
            [['index', 'code', 'name'], 'required'],
            [['index', 'code'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 1000],
            [['fias_id'], 'string', 'max' => 36],
        ];
    }

    public function getFias()
    {
        return $this->hasOne(Fias::class, ['fias_id' => 'fias_id']);
    }

    


    public function attributeLabels()
    {
        return [
            'index' => 'Индекс',
            'code' => 'Код улицы',
            'name' => 'Наименование',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    public static function streetIndex($code, $idBuilding, $idKorp = '')
    {
        preg_match('/\d+/', $idBuilding, $matches);
        $first_numbers = $matches[0] ?? '';
        $idBuilding = FiasDoma::removeAllBeforeNumbers($idBuilding);
        if ($idKorp) {
            $idBuildingArray = explode('/', $idBuilding);
            
            array_splice($idBuildingArray, 1, 0, $idKorp);
            $idBuilding = implode('/', $idBuildingArray);
        }
        $idBuilding = mb_strtolower($idBuilding);

        $filterQueryCode = "{$code}%";
        $tnFias = Fias::tableName();
        $tnFiasDoma = FiasDoma::tableName();
        $tnKladrCode = KladrCode::tableName();
        $kladr_codes = KladrCode::find()
            ->where(['LIKE', "{$tnKladrCode}.code", $filterQueryCode, false])
            ->select(["{$tnKladrCode}.id"]);
        $raw_fias_buldings = FiasDoma::find()
            ->andWhere(["{$tnFiasDoma}.code_id" => $kladr_codes,])
            ->andWhere(LikeQueryManager::getFullTextSearch(
                "{$tnFiasDoma}.name",
                $first_numbers
            ))
            ->limit(150)
            ->orderBy(["{$tnFiasDoma}.index" => SORT_DESC]);

        $fiasCodes = Fias::find()
            ->where(['LIKE', "{$tnFias}.code", $filterQueryCode, false])
            ->select(["{$tnFias}.fias_id"]);
        $raw_fias_buldings = FiasDoma::find()
            ->andWhere(["{$tnFiasDoma}.fias_id" => $fiasCodes,])
            ->andWhere(LikeQueryManager::getFullTextSearch(
                "{$tnFiasDoma}.name",
                $first_numbers
            ))
            ->limit(50)
            ->orderBy(['index' => SORT_DESC])
            ->union($raw_fias_buldings)
            ->all();

        if ($index = FiasDoma::extractIndexFromFiasDomaList($raw_fias_buldings, $idBuilding)) {
            return $index;
        }

        return 0;
    }

    





    private static function extractIndexFromFiasDomaList(array $rawFiasBuldings, string $idBuilding): string
    {
        foreach ($rawFiasBuldings as $bulding) {
            
            $names = array_map('trim', explode(',', mb_strtolower($bulding->name)));
            foreach ($names as $name) {
                if (FiasDoma::isNameMatches($idBuilding, $name)) {
                    return $bulding->index;
                }
            }
        }

        return '';
    }

    private static function removeAllBeforeNumbers(string $item): string
    {
        
        $len = strcspn($item, '0123456789');
        
        return substr($item, $len);
    }

    public static function isNameMatches(string $needle, string $haystack): bool
    {
        $name = str_replace(', ', '/', $haystack);
        $name = str_replace('стр', '/', $name);
        $name = str_replace('влд', '/', $name);
        $nameArray = explode('/', $name);
        $nameArray = array_filter($nameArray, function ($value) {
            
            return preg_match('/[0-9]/', $value);
        });
        $nameArray = array_map(function ($item) {
            return FiasDoma::removeAllBeforeNumbers($item);
        }, $nameArray);
        $name = implode('/', $nameArray);

        return $name == $needle;
    }

    




    public function getKladrCode()
    {
        return $this->hasOne(KladrCode::class, ['id' => 'code_id']);
    }
}
