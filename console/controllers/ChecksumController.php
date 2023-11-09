<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\components\ChecksumManager\ChecksumManager;

class ChecksumController extends Controller
{
    public function actionVendorChecksum()
    {
        $vendor_path = ChecksumManager::getVendorPath();
        echo ChecksumManager::calculateChecksum($vendor_path);
    }
}
