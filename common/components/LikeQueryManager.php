<?php

namespace common\components;

use Yii;
use yii\db\Expression;

class LikeQueryManager
{
    private const ESCAPE_CHARACTERS_FOR_FULL_TEXT_SEARCH = [
        '(' => '\(',
        ')' => '\)',
        ' ' => '\ ',
        '|' => '\|',
        '&' => '\&',
        ':' => '\:',
        '*' => '\*',
        '!' => '\!',
    ];

    


    public static function getActionName(): string
    {
        if (Yii::$app->db->driverName === 'pgsql') {
            return 'ILIKE';
        }

        return 'LIKE';
    }

    





    public static function getFullTextSearch(string $attribute, string $query)
    {
        if (Yii::$app->db->driverName !== 'pgsql') {
            return ['LIKE', $attribute, $query];
        }

        $searchString = strtr(
            $query,
            LikeQueryManager::ESCAPE_CHARACTERS_FOR_FULL_TEXT_SEARCH
        );

        return new Expression("
            to_tsvector(
                'russian',
                {$attribute}
            ) @@ to_tsquery(
                'russian',
                '{$searchString}'
            )
        ");
    }
}
