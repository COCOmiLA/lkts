<?php

namespace common\components;

use Throwable;
use Yii;
use yii\base\Component;

class ReleaseVersionProvider extends Component
{
    


    public function getVersion(): ?string
    {
        $version = Yii::$app->cache->get('GetReleaseVersion');
        if ($version) {
            return $version;
        }
        $version = $this->getRawVersion();
        if ($version) {
            Yii::$app->cache->set('GetReleaseVersion', $version, 3600);
        }
        return $version;
    }

    public function getRawVersion(): ?string
    {
        if (YII_ENV_DEV || !Yii::$app->soapClientAbit->isInitialized) {
            return Yii::$app->params['minimal_1C_version'];
        }
        $version1C = null;
        try {
            $result = Yii::$app->soapClientAbit->load('GetReleaseVersion');
            if (!empty($result->return)) {
                $version1C = $result->return;
            }
        } catch (Throwable $e) {
            Yii::error("Ошибка запроса версии из 1С: {$e->getMessage()}", 'getVersionFrom1C');
        }

        return $version1C;
    }

    public function clearVersionCache(): void
    {
        if (YII_ENV_DEV || !Yii::$app->soapClientAbit->isInitialized) {
            return;
        }
        if (Yii::$app->cache->exists('GetReleaseVersion')) {
            Yii::$app->cache->delete('GetReleaseVersion');
        }
    }

    public function isOneSServicesVersionMatches(): bool
    {
        $minimal_1C_version = Yii::$app->params['minimal_1C_version'] ?? null;
        $version1C = $this->getVersion();
        if (empty($minimal_1C_version) || empty($version1C)) {
            return true;
        }

        return version_compare($version1C, $minimal_1C_version) >= 0;
    }
}
