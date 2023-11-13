<?php

namespace common\models;

class MaxSpecialityType
{
    const TYPE_SPECIALITY = 'По направлениям подготовки';
    const TYPE_GROUP = 'По конкурсным группам';
    const TYPE_FACULTY = 'По факультетам';
    const TYPE_UGS = 'По направлениям подготовки/многопрофильным конкурсам';

    public static function getValues()
    {
        return [
            self::TYPE_SPECIALITY,
            self::TYPE_GROUP,
            self::TYPE_FACULTY,
            self::TYPE_UGS,
        ];
    }
}
