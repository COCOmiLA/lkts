<?php





namespace backend\controllers;

use backend\components\KladrLoader;
use backend\exceptions\DictionaryNoDataWarningHttpException;
use backend\models\DictionaryUpdateHistory;
use backend\models\search\DictionaryRestoreSearch;
use common\components\AppUpdate;
use common\components\dictionaryManager\ConfigurationValidateManager;
use common\components\ini\iniSet;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;





class DictionaryController extends Controller
{
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
            'time' => [
                'class' => 'common\components\EnvironmentManager\filters\TimeSyncCheckFilter',
            ],
        ];
    }

    public function actionIndex()
    {
        $iCanLoad_KLADR_from_file = true;
        $files = [
            'DOMA' => Yii::getAlias('@backend') . FileHelper::normalizePath('\web\conf\DOMA.dbf'),
            'KLADR' => Yii::getAlias('@backend') . FileHelper::normalizePath('\web\conf\KLADR.dbf'),
            'STREET' => Yii::getAlias('@backend') . FileHelper::normalizePath('\web\conf\STREET.dbf'),
        ];
        $filds = ['KLADR' => null, 'STREET' => null, 'DOMA' => null];

        foreach ($files as $key => $path) {
            if (!file_exists($path)) {
                $iCanLoad_KLADR_from_file = false;
                $filds[$key] = "В директории /backend/web/conf файл $key.dbf не найден.";
            }
        }

        return $this->render(
            'index',
            [
                'filds' => $filds,
                'iCanLoad_KLADR_from_1C' => Yii::$app->soapClientAbit->isServicesEnabled() && KladrLoader::isOneSFiasAvailable(),
                'iCanLoad_KLADR_from_file' => $iCanLoad_KLADR_from_file,
            ]
        );
    }

    public function actionGetDictionaryToUpdate()
    {
        if (Yii::$app->user->identity->isInRole(User::ROLE_ADMINISTRATOR)) {
            $updateManager = new AppUpdate();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $updateManager::DICTIONARY_UPDATE;
        }
        throw new ForbiddenHttpException();
    }

    public function actionUpdateOneDictionary($method)
    {
        ConfigurationValidateManager::enableUnspecifiedCodesError();
        Yii::$app->response->format = Response::FORMAT_JSON;

        iniSet::disableTimeLimit();
        iniSet::extendMemoryLimit();

        if (Yii::$app->user->identity->isInRole(User::ROLE_ADMINISTRATOR)) {
            if (empty($method)) {
                ConfigurationValidateManager::disableUnspecifiedCodesError();
                throw new BadRequestHttpException('No method');
            }
            $dictionaryManager = Yii::$app->dictionaryManager;
            if (method_exists($dictionaryManager, $method)) {
                try {
                    [$status, $error] = call_user_func(array($dictionaryManager, $method));
                } catch (\Throwable $e) {
                    $status = -1;
                    $error = $e;
                }
                $error_message = '';
                if ($status === 1) { 
                    ConfigurationValidateManager::disableUnspecifiedCodesError();
                    DictionaryUpdateHistory::setUpdateTime($method, time());
                    return [
                        'status' => true,
                        'error_message' => $error_message
                    ];
                } else {
                    if ($error) {
                        if ($error instanceof \Throwable) {
                            $error_message = "{$error->getMessage()}\n\n{$error->getTraceAsString()}";
                        } else {
                            $error_message = print_r($error, true);
                        }
                        Yii::error($error_message, 'DICTIONARY_UPDATE');
                        ConfigurationValidateManager::disableUnspecifiedCodesError();
                        return [
                            'status' => false,
                            'error_message' => $error_message
                        ];
                    }
                    ConfigurationValidateManager::disableUnspecifiedCodesError();
                    
                    throw new DictionaryNoDataWarningHttpException();
                }
            }
            ConfigurationValidateManager::disableUnspecifiedCodesError();
            throw new BadRequestHttpException('Невозможно найти метод: ' . $method);
        }
        ConfigurationValidateManager::disableUnspecifiedCodesError();
        throw new ForbiddenHttpException();
    }

    public function actionRestoreDictionary(int $dict_idx = null)
    {
        $model = new DictionaryRestoreSearch();
        $model->dict_index = $dict_idx;

        $indexed_names = $model->getIndexedNames();

        $model->load(Yii::$app->request->get());

        if (Yii::$app->request->isPost && !is_null($dict_idx)) {
            $selected_ids = Yii::$app->request->post('selection');
            if ($selected_ids) {
                $ref_types_to_restore = $model->selectedDictClass()::find()->where(['id' => $selected_ids])->all();
                foreach ($ref_types_to_restore as $item) {
                    $item->restoreDictionary();
                }
            }
            return $this->redirect(['/dictionary/restore-dictionary', 'dict_idx' => $model->dict_index]);
        }
        return $this->render('@backend/views/dictionary/restore_dictionary', [
            'dicts' => $indexed_names,
            'model' => $model
        ]);
    }
}
