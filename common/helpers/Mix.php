<?php

namespace common\helpers;

use Yii;
use yii\web\View;

class Mix
{
    private static $manifestDirectory = '/webpack'; 

    private static $manifest = null;

    public function registerMixFile(string $path)
    {
        Yii::$app->view->registerJsFile(
            Mix::mix($path),
            ['appendTimestamp' => false]
        );
    }

    







    public static function mix(string $path)
    {
        if (!self::starts_with($path, '/')) {
            $path = "/{$path}";
        }

        if (YII_ENV_DEV && file_exists(self::public_path('/hot'))) {
            $hmr_domain = file_get_contents(self::public_path('/hot'));
            $url = preg_replace("(^https?:)", "", $hmr_domain);
            
            return "{$url}{$path}";
        }

        if (!static::$manifest) {
            if (!file_exists($manifestPath = self::public_path('/mix-manifest.json'))) {
                throw new \Exception('The Mix manifest does not exist.');
            }

            static::$manifest = json_decode(file_get_contents($manifestPath), true);
        }

        if (!array_key_exists($path, static::$manifest)) {
            throw new \Exception(
                "Unable to locate Mix file: {$path}. Please check your " .
                'webpack.mix.js output paths and try again.'
            );
        }

        return static::$manifestDirectory . static::$manifest[$path];
    }

    






    public static function starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    private static function public_path($string)
    {
        return Yii::getAlias('@frontend') . '/web' . static::$manifestDirectory . $string;
    }
}