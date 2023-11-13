<?php


namespace common\components\AddressHelper;


use common\models\dictionary\Fias;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class AddressQueryBuilder
{
    private $conditions_array;
    private $associative_conditions_array;
    private $name_condition;
    private $limit = null;
    const CODES_HIERARCHY = [
        'region_code',
        'area_code',
        'city_code',
        'village_code',
    ];

    public function __construct()
    {
        $this->conditions_array = [];
        $this->name_condition = [];
        $this->associative_conditions_array = [];
    }

    public function setWhereCondition(array $condition)
    {
        if (!empty($condition)) {
            if (ArrayHelper::isAssociative($condition)) {
                $this->associative_conditions_array = ArrayHelper::merge($this->associative_conditions_array, $condition);
            } else {
                $this->conditions_array[] = $condition;
            }
        }

        return $this;
    }

    public function setLimit(?int $limit): AddressQueryBuilder
    {
        $this->limit = $limit;
        return $this;
    }

    public function setElemName($addressString)
    {
        $splitParams = self::getSplitAddressString($addressString);
        $this->name_condition = ArrayHelper::merge($this->name_condition, [
            'name' => array_shift($splitParams),
            'short' => array_pop($splitParams)
        ]);

        return $this;
    }

    public function getQuery()
    {
        $query = Fias::find()->andWhere($this->name_condition);
        foreach ($this->associative_conditions_array as $name => $value) {
            if ($value instanceof ActiveQuery) {
                if (!(clone $value)->exists()) {
                    continue;
                }
            }
            $condition = [];
            $condition[$name] = $value;
            $query = $query->andFilterWhere($condition);
        }
        foreach ($this->conditions_array as $passed_condition) {
            $query = $query->andFilterWhere($passed_condition);
        }
        
        $query->limit($this->limit);
        
        return $query;
    }

    public function getCount(): int
    {
        return $this->getQuery()->count();
    }

    public function getOne(): ?Fias
    {
        return $this->getQuery()->one();
    }

    public function getAll(): array
    {
        return $this->getQuery()->all();
    }

    public static function getSplitAddressString($addressString = null)
    {
        if (empty($addressString)) {
            return ['', ''];
        }
        $splitString = explode(' ', $addressString);
        foreach (AddressHelper::list() as $shortWord) {
            $splitString = explode(" {$shortWord}", $addressString);
            if (count($splitString) == 2 && strlen((string)$splitString[1]) < 1) {
                $splitString[1] = $shortWord;
                break;
            }
        }
        return $splitString;
    }
}