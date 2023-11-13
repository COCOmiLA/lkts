<?php


namespace common\components\RegularExpressionPasswordManager;







class RegularExpressionPasswordManager
{
    





    private static function mustContainLowercaseLetters(): array
    {
        return [
            'charList' => ['а-я', 'a-z'],
            'matchPattern' => '(?=.*[a-zа-я])'
        ];
    }

    





    private static function mustContainNumbers(): array
    {
        return [
            'charList' => ['0-9'],
            'matchPattern' => '(?=.*\d)'
        ];
    }

    





    private static function mustContainCapitalLetters(): array
    {
        return [
            'charList' => ['A-Z', 'А-Я'],
            'matchPattern' => '(?=.*[A-ZА-Я])'
        ];
    }

    





    private static function mustContainSpecialCharacters(): array
    {
        $specialChars = '_\-+=№#@$!%*?&\(\)\[\]\{\}';
        return [
            'charList' => [str_replace('\\', '', $specialChars)],
            'matchPattern' => "(?=.*[{$specialChars}])"
        ];
    }

    






    public static function buildRegex(
        bool $passwordMustContainNumbers = true,
        bool $passwordMustContainCapitalLetters = true,
        bool $passwordMustContainSpecialCharacters = true
    ): array {
        $matchPattern = '/^';

        [
            'charList' => $charList,
            'matchPattern' => $bufferMatchPattern,
        ] = RegularExpressionPasswordManager::mustContainLowercaseLetters();
        $matchPattern .= $bufferMatchPattern;

        if ($passwordMustContainNumbers) {
            [
                'charList' => $bufferCharList,
                'matchPattern' => $bufferMatchPattern,
            ] = RegularExpressionPasswordManager::mustContainNumbers();
            $matchPattern .= $bufferMatchPattern;
            $charList = array_merge($charList, $bufferCharList);
        }

        if ($passwordMustContainCapitalLetters) {
            [
                'charList' => $bufferCharList,
                'matchPattern' => $bufferMatchPattern,
            ] = RegularExpressionPasswordManager::mustContainCapitalLetters();
            $matchPattern .= $bufferMatchPattern;
            $charList = array_merge($charList, $bufferCharList);
        }

        if ($passwordMustContainSpecialCharacters) {
            [
                'charList' => $bufferCharList,
                'matchPattern' => $bufferMatchPattern,
            ] = RegularExpressionPasswordManager::mustContainSpecialCharacters();
            $matchPattern .= $bufferMatchPattern;
            $charList = array_merge($charList, $bufferCharList);
        }

        $matchPattern .= '.*$/';

        return [
            'charList' => $charList,
            'matchPattern' => $matchPattern,
        ];
    }
}
