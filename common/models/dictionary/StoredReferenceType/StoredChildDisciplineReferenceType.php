<?php


namespace common\models\dictionary\StoredReferenceType;

class StoredChildDisciplineReferenceType extends StoredDisciplineReferenceType
{
    public static function tableAlias()
    {
        return '{{%' . StoredChildDisciplineReferenceType::tableAliasForJoin() . '}}';
    }

    public static function tableAliasForJoin()
    {
        return 'discipline_reference_type_child';
    }
}
