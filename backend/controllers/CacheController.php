<?php

namespace backend\controllers;

use common\components\FilesWorker\FilesWorker;
use common\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\caching\TagDependency;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;





class CacheController extends Controller
{
    
    private $filesWorker = null;

    public function init()
    {
        $this->filesWorker = new FilesWorker(['.gitignore']);
        parent::init();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMINISTRATOR]
                    ],
                ],
            ],
        ];
    }

    


    public function actionIndex()
    {
        $dataProvider = new ArrayDataProvider(['allModels' => $this->findCaches()]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    






    public function actionFlushCache($id)
    {
        if ($this->getCache($id)->flush()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'кэш был успешно сброшен'),
                'options' => ['class' => 'alert-success']
            ]);
        };
        return $this->redirect(['index']);
    }

    







    public function actionFlushCacheKey($id, $key)
    {
        if ($this->getCache($id)->delete($key)) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Запись была успешно удалена из кэша'),
                'options' => ['class' => 'alert-success']
            ]);
        };
        return $this->redirect(['index']);
    }

    







    public function actionFlushCacheTag($id, $tag)
    {
        TagDependency::invalidate($this->getCache($id), $tag);
        Yii::$app->session->setFlash('alert', [
            'body' => Yii::t('backend', 'TagDependency был инвалидирован'),
            'options' => ['class' => 'alert-success']
        ]);
        return $this->redirect(['index']);
    }

    




    public function actionClearFrontendAsset()
    {
        $path = FileHelper::normalizePath(Yii::getAlias('@frontendAssets'));

        if ($this->filesWorker::purgeDirectoryContent($path)) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Removal was successful'),
                'options' => ['class' => 'alert-success']
            ]);
        } else {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'An error occurred: `{errorMessage}`. Contact your administrator.', [
                    'errorMessage' => Yii::t('backend', 'Failed to completely delete assets "{assetPath}"', [
                        'assetPath' => $path,
                    ]),
                ]),
                'options' => ['class' => 'alert-danger']
            ]);
        }
        return $this->redirect(['index']);
    }

    




    public function actionClearBackendAsset()
    {
        $path = FileHelper::normalizePath(Yii::getAlias('@backendAssets'));

        if ($this->filesWorker::purgeDirectoryContent($path)) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Removal was successful'),
                'options' => ['class' => 'alert-success']
            ]);
        } else {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'An error occurred: `{errorMessage}`. Contact your administrator.', [
                    'errorMessage' => Yii::t('backend', 'Failed to completely delete assets "{assetPath}"', [
                        'assetPath' => $path,
                    ]),
                ]),
                'options' => ['class' => 'alert-danger']
            ]);
        }
        return $this->redirect(['index']);
    }

    







    protected function getCache($id)
    {
        if (!in_array($id, array_keys($this->findCaches()))) {
            throw new HttpException(400, 'Given cache name is not a name of cache component');
        }
        return Yii::$app->get($id);
    }

    





    private function findCaches(array $cachesNames = [])
    {
        $caches = [];
        $components = Yii::$app->getComponents();
        $findAll = ($cachesNames == []);

        foreach ($components as $name => $component) {
            if (!$findAll && !in_array($name, $cachesNames)) {
                continue;
            }

            if ($component instanceof Cache) {
                $caches[$name] = ['name' => $name, 'class' => get_class($component)];
            } elseif (is_array($component) && isset($component['class']) && $this->isCacheClass($component['class'])) {
                $caches[$name] = ['name' => $name, 'class' => $component['class']];
            } elseif (is_string($component) && $this->isCacheClass($component)) {
                $caches[$name] = ['name' => $name, 'class' => $component];
            }
        }

        return $caches;
    }

    





    private function isCacheClass($className)
    {
        return is_subclass_of($className, Cache::class);
    }

    




    public function actionClearDatabaseSchemaCache()
    {
        Yii::$app->db->schema->refresh();

        Yii::$app->session->setFlash('alert', [
            'body' => Yii::t('backend', 'Очистка завершена успешно'),
            'options' => ['class' => 'alert-success']
        ]);
        return $this->redirect(['index']);
    }
}
