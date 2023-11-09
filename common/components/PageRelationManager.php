<?php


namespace common\components;


class PageRelationManager
{
    public const RELATED_ENTITY_QUESTIONARY = 'questionary';
    public const RELATED_ENTITY_EGE = 'ege';
    public const RELATED_ENTITY_APPLICATION = 'application';
    public const RELATED_ENTITY_EDUCATION = 'education';
    public const RELATED_ENTITY_REGISTRATION = 'registration';
    public const RELATED_ENTITY_OLYMPIAD = 'olymp';
    public const RELATED_ENTITY_PREFERENCE = 'preference';
    public const RELATED_ENTITY_TARGET_RECEPTION = 'target';

    protected static $RELATIONS = [
        self::RELATED_ENTITY_REGISTRATION => 'Страница регистрации',
        self::RELATED_ENTITY_QUESTIONARY => 'Анкета',
        self::RELATED_ENTITY_EDUCATION => 'Документы об образовании',
        self::RELATED_ENTITY_OLYMPIAD => 'Поступление без вступительных испытаний',
        self::RELATED_ENTITY_PREFERENCE => 'Льготы',
        self::RELATED_ENTITY_TARGET_RECEPTION => 'Целевые договоры',
        self::RELATED_ENTITY_APPLICATION => 'Заявление',
        self::RELATED_ENTITY_EGE => 'Вступительные испытания и ЕГЭ',
    ];


    public static function GetRelatedList(bool $only_application_relations = false)
    {
        $return = static::$RELATIONS;
        if ($only_application_relations) {
            unset($return[self::RELATED_ENTITY_QUESTIONARY]);
            unset($return[self::RELATED_ENTITY_REGISTRATION]);
        }
        return $return;
    }

    public static function GetRelatedTitle($relation)
    {
        return static::GetRelatedList()[$relation] ?? 'Неизвестно';
    }

    public static function GetFullRelatedListForApplication(): array
    {
        return [
            static::RELATED_ENTITY_APPLICATION,
            static::RELATED_ENTITY_EDUCATION,
            static::RELATED_ENTITY_EGE,
            static::RELATED_ENTITY_TARGET_RECEPTION,
            static::RELATED_ENTITY_OLYMPIAD,
            static::RELATED_ENTITY_PREFERENCE,
        ];
    }
}