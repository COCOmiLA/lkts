<?php

namespace common\services;

use common\components\dictionaryManager\dictionaryManager;
use yii\base\BaseObject;

class NamesManagementService extends BaseObject
{
    private dictionaryManager $dictionaryManager;

    public function __construct(dictionaryManager $dictionaryManager, $config = [])
    {
        parent::__construct($config);
        $this->dictionaryManager = $dictionaryManager;
    }

    public function getFullPackageAchievementCommentColumnName(): string
    {
        $version = $this->dictionaryManager->GetInterfaceVersion('PostEntrantPackage');
        if (version_compare($version, '0.0.19.3') >= 0) {
            return 'Comment';
        }
        return 'Сomment';
    }

    public function getPropertyAttributeValueColumnName(): string
    {
        $version = $this->dictionaryManager->GetInterfaceVersion('GetLapResults');
        if (version_compare($version, '0.0.19.3') >= 0) {
            return 'PropertyAttributeValue';
        }
        return 'PropertyАttributeValue';
    }

    public function getPropertyAttributeNameColumnName(): string
    {
        $version = $this->dictionaryManager->GetInterfaceVersion('GetLapResults');
        if (version_compare($version, '0.0.19.3') >= 0) {
            return 'PropertyAttributeName';
        }
        return 'PropertyАttributeName';
    }
}