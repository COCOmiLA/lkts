<?php

namespace backend\controllers;

use backend\components\KladrLoader;
use backend\models\CommonSettings;
use backend\models\Consent;
use backend\models\DocumentTemplate;
use backend\models\MasterSystemManagerInterfaceSetting;
use backend\models\PortalManagerInterfaceSetting;
use backend\models\SortedElementPage;
use backend\models\StorageDictionary;
use cheatsheet\Time;
use common\components\AdmissionCampaignDictionaryManager\AdmissionCampaignDictionaryManager;
use common\components\AttachmentManager;
use common\components\ChecksumManager\ChecksumManager;
use common\components\ChecksumManager\FilesChecksumReport;
use common\components\EnvironmentManager\filters\TimeSyncCheckFilter;
use common\components\filesystem\FilterFilename;
use common\components\TextSettingsManager\TextSettingsManager;
use common\models\AttachmentType;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use common\models\errors\RecordNotValid;
use common\models\notification\NotificationSetting;
use common\models\notification\NotificationType;
use common\models\Recaptcha;
use common\models\RecaptchaForm;
use common\models\Rolerule;
use common\models\RoleruleForm;
use common\models\settings\ApplicationsSettings;
use common\models\settings\AuthSetting;
use common\models\settings\ChangeHistorySettings;
use common\models\settings\ChatSettings;
use common\models\settings\CodeSetting;
use common\models\settings\ParentDataSetting;
use common\models\settings\SandboxSetting;
use common\models\settings\StudentSideLinks;
use common\models\settings\TextSetting;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use Throwable;
use Yii;
use yii\base\Model;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\Controller;

class SettingsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post']
                ]
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => [User::ROLE_ADMINISTRATOR]
                ]],
            ],
            'time' => ['class' => TimeSyncCheckFilter::class],
        ];
    }

    public function actionDeleteSandboxTemplate(string $type)
    {
        $result = false;
        $template_to_delete = null;
        if ($type == 'consent_template') {
            $template_to_delete = Consent::find()->one();
        }

        if (!is_null($template_to_delete)) {
            $template_to_delete->deleteAttachedFile();
            $result = $template_to_delete->save();
        }
        return $this->asJson($result);
    }

    public function actionSandbox()
    {
        $sandbox_enabled = SandboxSetting::findOne(['name' => 'sandbox_enabled']);
        if (Yii::$app->request->isPost) {
            $sandbox_enabled->load(Yii::$app->request->post());
            if (!$sandbox_enabled->save()) {
                throw new RecordNotValid($sandbox_enabled);
            }
        }
        return $this->render('sandbox', [
            'sandbox_enabled' => $sandbox_enabled
        ]);
    }

    public function actionDownloadTemplate($name)
    {
        $file = DocumentTemplate::findOne(['name' => $name]);
        if (!is_null($file)) {
            $abs_path = $file->getAbsPath();
            if ($abs_path && file_exists($abs_path) && !is_dir($abs_path)) {
                return Yii::$app->response->sendFile(
                    $abs_path,
                    FilterFilename::sanitize(ArrayHelper::getValue($file, 'linkedFile.upload_name', '')),
                    [
                        'mimeType' => $file->getMimeType(),
                        'inline' => ArrayHelper::getValue($file, 'linkedFile.extension')
                    ]
                );
            }
        }
        throw new UserException('Бланк отзыва согласия на зачисление недоступен. Обратитесь к администратору.');
    }

    public function actionAuth()
    {
        $use_email = AuthSetting::findOne(['name' => 'use_email']);
        $canNotInputLatinFio = AuthSetting::findOne(['name' => 'can_not_input_latin_fio']);
        $confirmEmail = AuthSetting::findOne(['name' => 'confirm_email']);
        $confirmPassword = AuthSetting::findOne(['name' => 'confirm_password']);
        $minimalPasswordLength = AuthSetting::findOne(['name' => 'minimal_password_length']);
        $passwordMustContainNumbers = AuthSetting::findOne(['name' => 'password_must_contain_numbers']);
        $passwordMustContainCapitalLetters = AuthSetting::findOne(['name' => 'password_must_contain_capital_letters']);
        $passwordMustContainSpecialCharacters = AuthSetting::findOne(['name' => 'password_must_contain_special_characters']);
        $confirmEmailTokenTTL = AuthSetting::findOne(['name' => 'confirm_email_token_ttl']);
        $allowRememberMe = AuthSetting::findOne(['name' => 'allow_remember_me']);
        $identityCookieDuration = AuthSetting::findOne(['name' => 'identity_cookie_duration']);

        $models = [
            $use_email,
            $canNotInputLatinFio,
            $confirmEmail,
            $confirmPassword,
            $minimalPasswordLength,
            $passwordMustContainNumbers,
            $passwordMustContainCapitalLetters,
            $passwordMustContainSpecialCharacters,
            $confirmEmailTokenTTL,
            $allowRememberMe,
            $identityCookieDuration
        ];

        if (Yii::$app->request->isPost) {
            $use_email_value = Yii::$app->request->post('use_email');
            $canNotInputLatinFioValue = Yii::$app->request->post('canNotInputLatinFio');
            $confirmEmailValue = Yii::$app->request->post('confirmEmail');
            $confirmPasswordValue = Yii::$app->request->post('confirmPassword');
            $minimalPasswordLengthValue = Yii::$app->request->post('minimalPasswordLength');
            $passwordMustContainNumbersValue = Yii::$app->request->post('passwordMustContainNumbers');
            $passwordMustContainCapitalLettersValue = Yii::$app->request->post('passwordMustContainCapitalLetters');
            $passwordMustContainSpecialCharactersValue = Yii::$app->request->post('passwordMustContainSpecialCharacters');
            $confirmEmailTokenTTLValue = Yii::$app->request->post('confirmEmailTokenTTL');

            $use_email->value = $use_email_value;
            $canNotInputLatinFio->value = $canNotInputLatinFioValue;
            $confirmEmail->value = $confirmEmailValue;
            $confirmPassword->value = $confirmPasswordValue;
            $minimalPasswordLength->value = $minimalPasswordLengthValue;
            $passwordMustContainCapitalLetters->value = $passwordMustContainCapitalLettersValue;
            $passwordMustContainNumbers->value = $passwordMustContainNumbersValue;
            $passwordMustContainSpecialCharacters->value = $passwordMustContainSpecialCharactersValue;

            $allowRememberMe->value = Yii::$app->request->post('allow_remember_me');
            if ($allowRememberMe->value) {
                $duration_select = Yii::$app->request->post('identity_cookie_duration_select');
                $identityCookieDuration->value = empty($duration_select) ?
                    Yii::$app->request->post('identity_cookie_duration', Time::SECONDS_IN_AN_HOUR)
                    : $duration_select;
            }

            if ($confirmEmailTokenTTLValue !== null) {
                $confirmEmailTokenTTL->value = $confirmEmailTokenTTLValue;
            }

            foreach ($models as $setting) {
                if (!$setting->save()) {
                    Yii::$app->session->setFlash('alert', [
                        'body' => Yii::t(
                            'backend',
                            'Возникла ошибка сохранения настроек авторизации. Обратитесь к администратору.'
                        ),
                        'options' => ['class' => 'alert-danger']
                    ]);
                    Yii::error("Ошибка при сохранении настройки авторизации: {$setting->name}:" . PHP_EOL
                        . VarDumper::dumpAsString($setting->errors), 'actionAuth');
                }
            }
        }

        return $this->render(
            'auth',
            [
                'use_email' => $use_email,
                'confirmEmail' => $confirmEmail,
                'confirmPassword' => $confirmPassword,
                'canNotInputLatinFio' => $canNotInputLatinFio,
                'confirmEmailTokenTTL' => $confirmEmailTokenTTL,
                'minimalPasswordLength' => $minimalPasswordLength,
                'passwordMustContainNumbers' => $passwordMustContainNumbers,
                'passwordMustContainCapitalLetters' => $passwordMustContainCapitalLetters,
                'passwordMustContainSpecialCharacters' => $passwordMustContainSpecialCharacters,
                'allowRememberMe' => $allowRememberMe,
                'identityCookieDuration' => $identityCookieDuration
            ]
        );
    }

    public function actionResetTextSettings()
    {
        if (TextSettingsManager::resetToDefaultSettings()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Настройки текстов восстановлены.'),
                'options' => ['class' => 'alert-success'],
            ]);
        } else {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Возникла ошибка сохранения.'),
                'options' => ['class' => 'alert-danger'],
            ]);
        }

        return $this->redirect(Url::to('text'));
    }

    public function actionText()
    {
        $selected_language = Yii::$app->request->get('language') ?? 'ru';
        $selected_application_type = Yii::$app->request->get('application_type') ?? 0;

        $application_types = ArrayHelper::merge([TextSetting::APPLICATION_TYPE_DEFAULT => 'По умолчанию'], ArrayHelper::map(ApplicationType::find()->active()->all(), 'id', 'name'));

        $languages = Yii::$app->localizationManager->getAvailableLocales(true);

        if (Yii::$app->request->isPost) {
            foreach (Yii::$app->request->post("TextSetting") as $setting_encoded_key => $text_setting) {
                $settings = json_decode((string)$setting_encoded_key, true);
                $text = TextSetting::findOne($settings);
                if (!$text) {
                    $text = new TextSetting($settings);
                }
                $text->value = $text_setting['value'];

                $text->save();
            }
            Yii::$app->configurationManager->resetTextCache();
        }
        $all_texts = [];

        if ($selected_language !== null && $selected_application_type !== null) {
            $all_texts = Yii::$app->configurationManager->getAllTextNames();
            $all_texts = ArrayHelper::index($all_texts, null, 'category');
        }
        $categories = TextSetting::getCategories();

        return $this->render("text", [
            'application_types' => $application_types,
            'languages' => $languages,
            'categories' => $categories,
            'all_texts' => $all_texts,
            'language' => $selected_language,
            'application_type' => $selected_application_type,
        ]);
    }

    







    public function actionStudentsidelinks()
    {
        if (Yii::$app->request->isPost) {
            if (Yii::$app->request->post('table_button_submit') == 'save') {
                $linksFromPost = ArrayHelper::getValue(Yii::$app->request->post(), (new StudentSideLinks())->formName(), []);
                foreach ($linksFromPost as $key => $student_links) {
                    $link = StudentSideLinks::findOne(['id' => (int)$student_links['id']]);
                    $link->description = $student_links['description'];
                    $link->url = $student_links['url'];
                    $link->number = $student_links['number'];

                    $link->save();
                }
            } elseif (Yii::$app->request->post('table_button_submit') == 'add') {
                $link = new StudentSideLinks();

                $link->save();
            } elseif (Yii::$app->request->post('table_button_submit') == 'delete' && Yii::$app->request->post('selection')) {
                $selection = Yii::$app->request->post('selection');
                $linksFromPost = ArrayHelper::getValue(Yii::$app->request->post(), (new StudentSideLinks())->formName(), []);
                foreach ($linksFromPost as $key => $student_links) {
                    if (in_array($key, $selection)) {
                        $link = StudentSideLinks::findOne(['id' => (int)$student_links['id']]);
                        $link->delete();
                    }
                }
            }

            SortedElementPage::updateElements();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => StudentSideLinks::find(),
            'pagination' => false,
        ]);
        return $this->render(
            'student_side_links',
            ['dataProvider' => $dataProvider]
        );
    }

    public function actionCode()
    {

        if (Yii::$app->request->isPost) {
            foreach (Yii::$app->request->post("CodeSetting") as $code_setting) {
                $code = CodeSetting::findOne(['id' => (int)$code_setting['id']]);
                $code->value = $code_setting['value'];

                if ($code->save()) {
                    
                    if ($code->name == 'paid_contract_document_type') {
                        $docType = DocumentType::findByUID($code->value);
                        $att_type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY);
                        $att_type->document_type_guid = $docType->ref_key ?? null;
                        $att_type->document_type_id = $docType->id ?? null;
                        $att_type->save();
                    }
                }
            }
            Yii::$app->configurationManager->resetCodesCache();
        }

        $codes = CodeSetting::find()->all();
        return $this->render("code", [
            'codes' => $codes,
        ]);
    }

    public function actionScan()
    {
        $scansDataProvider = new ActiveDataProvider([
            'query' => AttachmentType::find(),
        ]);
        return $this->render("scan", [
            'scansDataProvider' => $scansDataProvider,
        ]);
    }

    public function actionDeleteScan($id)
    {
        $type = AttachmentType::findOne($id);
        if (isset($type)) {
            $type->delete();
        }

        return $this->redirect(Url::toRoute('settings/scan'), 302);
    }


    public function actionKladr()
    {
        $errors = KladrLoader::loadKladr('file');
        if (empty($errors)) {
            Yii::$app->session->setFlash('successFias', 'Справочник "КЛАДР" установлен успешно');
        } else {
            Yii::$app->session->setFlash('errorFias', 'Ошибка установки справочника "КЛАДР": ' . $errors[0]);
        }
        return $this->redirect(['/dictionary/index']);
    }

    public function actionRolerule($isAbit = false)
    {
        $rolerule_error = false;
        $isAbit = (bool)$isAbit;
        if (!empty(Yii::$app->db->getTableSchema('rolerule'))) {
            $model = new RoleruleForm();

            $rolerule = Rolerule::find()->limit(1)->one();
            if ($model->load(Yii::$app->request->post())) {
                if (isset($model->student)) {
                    $rolerule->student = $model->student;
                }
                if (isset($model->teacher)) {
                    $rolerule->teacher = $model->teacher;
                }
                if (isset($model->abiturient)) {
                    $rolerule->abiturient = $model->abiturient;
                }

                $rolerule->save();
            } else {
                $model->student = $rolerule->student;
                $model->teacher = $rolerule->teacher;
                $model->abiturient = $rolerule->abiturient;
            }
        } else {
            $rolerule_error = true;
        }
        return $this->render("rolerule", [
            'model' => $model,
            'isAbit' => $isAbit,
            'rolerule_error' => $rolerule_error,
        ]);
    }

    public function actionRecaptcha()
    {
        $recaptchaForm = new RecaptchaForm();

        $recaptchaForm->site_key_v2 = getenv('SITE_KEY_V2');
        $recaptchaForm->site_key_v3 = getenv('SITE_KEY_V3');
        $recaptchaForm->server_key_v2 = getenv('SERVER_KEY_V2');
        $recaptchaForm->server_key_v3 = getenv('SERVER_KEY_V3');

        if (
            $recaptchaForm->load(Yii::$app->request->post()) &&
            Recaptcha::loadFromPost(Yii::$app->request->post())
        ) {
            $envEditor = Yii::$app->env;
            $envEditor = $envEditor->load(FileHelper::normalizePath('../../.env'));
            if (strlen((string)$recaptchaForm->site_key_v2) > 0) {
                $envEditor = $envEditor->setKey('SITE_KEY_V2', $recaptchaForm->site_key_v2);
            }
            if (strlen((string)$recaptchaForm->site_key_v3) > 0) {
                $envEditor = $envEditor->setKey('SITE_KEY_V3', $recaptchaForm->site_key_v3);
            }
            if (strlen((string)$recaptchaForm->site_key_v2) > 0) {
                $envEditor = $envEditor->setKey('SERVER_KEY_V2', $recaptchaForm->server_key_v2);
            }
            if (strlen((string)$recaptchaForm->site_key_v3) > 0) {
                $envEditor = $envEditor->setKey('SERVER_KEY_V3', $recaptchaForm->server_key_v3);
            }
            $envEditor = $envEditor->save();
        }

        $recaptchas = [];
        if (!empty(Yii::$app->db->getTableSchema('recaptcha'))) {
            $recaptchas = Recaptcha::find()->all();
        }

        return $this->render(
            'recaptcha',
            [
                'recaptchaForm' => $recaptchaForm,
                'recaptchas' => $recaptchas
            ]
        );
    }

    public function actionSetIndexPage()
    {
        return $this->render(
            'set_index_page',
            ['rolesList' => User::getAllStudentSideRole()]
        );
    }

    public function actionStorage()
    {
        $hasError = false;

        $storageDictionary = getenv('STORAGE_DICTIONARY');
        $model = new StorageDictionary($storageDictionary ?: '');

        $oldStoragePath = $model->storagePath;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $hasError = !$model->save();
                if ($hasError) {
                    Yii::$app->session->setFlash('alert', [
                        'body' => 'Возникла ошибка сохранения.',
                        'options' => ['class' => 'alert-danger'],
                    ]);
                } else {
                    Yii::$app->session->setFlash('alert', [
                        'body' => 'Сохранение прошло удачно',
                        'options' => ['class' => 'alert-success'],
                    ]);
                    if ($oldStoragePath != $model->storagePath) {

                        if (empty($oldStoragePath)) {
                            $oldStoragePath = FileHelper::normalizePath(__DIR__ . '..\..\..\storage\web');
                        } else {
                            $oldStoragePath = FileHelper::normalizePath("{$oldStoragePath}\web");
                        }
                        if (empty($model->storagePath)) {
                            $newStoragePath = FileHelper::normalizePath(__DIR__ . '..\..\..\storage\web');
                        } else {
                            $newStoragePath = FileHelper::normalizePath("{$model->storagePath}\web");
                        }
                        Yii::$app->session->setFlash('alert-info', [
                            'body' => "
                                При указании нового пути все системные файлы будут перемещены автоматически,
                                однако старые файлы скан-копий пользователей необходимо будет перенести из папки <em>'{$oldStoragePath}'</em> в новую папку,
                                где будут храниться файлы портала <em>'$newStoragePath'</em>
                            ",
                            'options' => ['class' => 'alert-info'],
                        ]);
                    }
                }
            } else {
                Yii::$app->session->setFlash('alert', [
                    'body' => 'Возникла ошибка валидации.',
                    'options' => ['class' => 'alert-danger'],
                ]);
            }
        }
        return $this->render(
            'storage',
            [
                'model' => $model,
                'hasError' => $hasError,
            ]
        );
    }

    public function actionMasterSystemManagerInterface()
    {
        $settings = MasterSystemManagerInterfaceSetting::find()
            ->indexBy('id')->all();
        $portalManagerSettings = PortalManagerInterfaceSetting::find()
            ->indexBy('id')->all();

        $request = Yii::$app->request;

        if ($request->post('MasterSystemManagerInterfaceSetting')) {
            Model::loadMultiple($settings, $request->post());
            foreach ($settings as $setting) {
                if (!$setting->save()) {
                    Yii::$app->session->setFlash('alert', [
                        'body' => Yii::t(
                            'backend',
                            'Возникла ошибка сохранения настроек интерфейса модератора 1С. Обратитесь к администратору.'
                        ),
                        'options' => ['class' => 'alert-danger']
                    ]);
                    Yii::error("Ошибка при сохранении настроек интерфейса модератора 1С", 'actionMasterSystemManagerInterface');
                }
            }
        }

        if ($request->post('PortalManagerInterfaceSetting')) {
            Model::loadMultiple($portalManagerSettings, $request->post());
            foreach ($portalManagerSettings as $setting) {
                if (!$setting->save()) {
                    Yii::$app->session->setFlash('alert', [
                        'body' => Yii::t(
                            'backend',
                            'Возникла ошибка сохранения настроек интерфейса модератора портала. Обратитесь к администратору.'
                        ),
                        'options' => ['class' => 'alert-danger']
                    ]);
                    Yii::error("Ошибка при сохранении настроек интерфейса модератора портала", 'actionMasterSystemManagerInterface');
                }
            }
        }

        $isMasterSystemManagerEnabled = Yii::$app->configurationManager->getMasterSystemManagerSetting('use_master_system_manager_interface');

        return $this->render(
            'master_system_manager_interface',
            [
                'settings' => $settings,
                'isMasterSystemManagerEnabled' => $isMasterSystemManagerEnabled,
                'portalManagerSettings' => $portalManagerSettings
            ]
        );
    }

    public function actionUpdateAdmissionCampaignTokens()
    {
        $result = AdmissionCampaignDictionaryManager::FetchAdmissionCampaign();

        if ($result === false) {
            throw new UserException('Ошибка при выполнении метода GetPK. Внутренняя ошибка сервера.');
        }

        if (isset($result->return->UniversalResponse->Complete) && $result->return->UniversalResponse->Complete == '0') {
            throw new UserException('Ошибка при выполнении метода GetPK: ' . $result->return->UniversalResponse->Description . ' ' . PHP_EOL . print_r($result, true));
        }

        if (!is_array($result->return->PK)) {
            $result->return->PK = [$result->return->PK];
        }

        $notFoundAdmissionCampaigns = [];

        foreach ($result->return->PK as $campaign) {

            $admission_campaign = AdmissionCampaignDictionaryManager::FindAdmissionCampaign($campaign);

            if ($admission_campaign !== null) {
                $admission_campaign->api_token = (string)$campaign->CampaignToken;

                $admission_campaign->save(true, ['api_token']);
            } else {
                $notFoundAdmissionCampaigns[] = (string)$campaign->Description;
            }
        }

        if ($notFoundAdmissionCampaigns) {
            Yii::$app->session->setFlash('notFoundAdmissionCampaigns', $notFoundAdmissionCampaigns);
        }

        Yii::$app->session->setFlash('masterSystemManagerSuccessMessage', 'Токены приемных кампаний были успешно обновлены.');
        return $this->redirect(['/settings/master-system-manager-interface']);
    }

    public function actionChecksum()
    {
        $model = ChecksumManager::getCurrentVendorChecksum();

        if (\Yii::$app->request->isPost) {
            ChecksumManager::saveChecksum($model);
            $this->redirect(Url::to(['checksum']));
        }

        return $this->render('checksum', ['model' => $model]);
    }

    public function actionDownloadChecksumReport()
    {
        $report = new FilesChecksumReport(ChecksumManager::getVendorPath());

        return \Yii::$app->response->sendContentAsFile(
            $report->asJson(),
            'checksum_' . date('Ymd_His') . '.json',
            ['mimeType' => 'application/json']
        );
    }

    public function actionNotification()
    {
        $types = NotificationType::find()->all();
        $request_interval = NotificationSetting::findOne(['name' => 'request_interval']);
        $enable_widget = NotificationSetting::findOne(['name' => 'enable_widget']);

        $success = true;

        if (Yii::$app->request->isPost) {
            if (Model::loadMultiple($types, Yii::$app->request->post())) {
                foreach ($types as $type) {
                    $success = $success && $type->save(true, ['enabled']);
                }
            }

            $request_interval->value = Yii::$app->request->post('request_interval');
            $success = $success && $request_interval->save(true, ['value']);

            $enable_widget->value = Yii::$app->request->post('enable_widget');
            $success = $success && $enable_widget->save(true, ['value']);

            if (!$success) {
                Yii::$app->session->setFlash('alert', [
                    'body' => Yii::t(
                        'backend',
                        'Возникла системная ошибка. Обратитесь к администратору.'
                    ),
                    'options' => ['class' => 'alert-danger']
                ]);
                Yii::error("Ошибка при сохранении настроек уведомлений", 'actionNotification');
            }
        }

        return $this->render('notification', [
            'types' => $types,
            'request_interval' => $request_interval,
            'enable_widget' => $enable_widget
        ]);
    }

    public function actionQuestionary()
    {
        $settings = ArrayHelper::index(
            QuestionarySettings::find()->all(),
            function (QuestionarySettings $setting) {
                

                return $setting->name;
            }
        );

        if (Yii::$app->request->isPost) {
            Model::loadMultiple($settings, Yii::$app->request->post());
            foreach ($settings as $setting) {
                

                if (!$setting->save()) {
                    throw new RecordNotValid($setting);
                }
            }
        }

        return $this->render(
            'questionary',
            ['settings' => $settings]
        );
    }

    public function actionMain()
    {
        $setting = CommonSettings::getInstance();

        if (Yii::$app->request->isPost) {
            $setting->load(Yii::$app->request->post());
            $setting->save();
        }

        return $this->render(
            'main',
            ['setting' => $setting]
        );
    }

    public function actionPhpInfo()
    {
        return $this->render('phpinfo');
    }

    public function actionChat()
    {
        $settings = ArrayHelper::index(
            ChatSettings::find()->all(),
            function (ChatSettings $setting) {
                

                return $setting->id;
            }
        );

        if (Yii::$app->request->isPost) {
            Model::loadMultiple($settings, Yii::$app->request->post());
            foreach ($settings as $setting) {
                

                if (!$setting->save()) {
                    throw new RecordNotValid($setting);
                }
            }
        }

        return $this->render(
            'chat-settings',
            compact('settings')
        );
    }

    public function actionChangeHistorySettings()
    {
        $settings = ArrayHelper::index(
            ChangeHistorySettings::find()->all(),
            function (ChangeHistorySettings $setting) {
                

                return $setting->id;
            }
        );

        if (Yii::$app->request->isPost) {
            Model::loadMultiple($settings, Yii::$app->request->post());

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($settings as $setting) {
                    

                    if (!$setting->save()) {
                        throw new RecordNotValid($setting);
                    }
                }

                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render(
            'change-history-settings',
            compact('settings')
        );
    }

    public function actionPhpRequirements()
    {
        return $this->render('requirements');
    }

    public function actionParentData()
    {
        if (Yii::$app->request->isPost) {
            foreach (Yii::$app->request->post('ParentDataSetting') as $code_setting) {
                $code = ParentDataSetting::findOne(['id' => (int)$code_setting['id']]);
                if (isset($code_setting['value'])) {
                    $code->value = $code_setting['value'];
                    if (!$code->save()) {
                        throw new RecordNotValid($code);
                    }
                }
            }
        }

        $codes = ParentDataSetting::find()->all();

        return $this->render("parent_data", [
            'codes' => $codes,
        ]);
    }

    public function actionApplicationSettings()
    {
        $settings = ArrayHelper::index(
            ApplicationsSettings::find()->all(),
            function (ApplicationsSettings $setting) {
                return $setting->id;
            }
        );
        if (Yii::$app->request->isPost) {
            Model::loadMultiple($settings, Yii::$app->request->post());
            foreach ($settings as $setting) {
                

                if (!$setting->save()) {
                    throw new RecordNotValid($setting);
                }
            }
        }

        return $this->render(
            'application-settings',
            ['settings' => $settings]
        );
    }

    public function actionDocumentCheckStatusAliases()
    {
        $settings = ArrayHelper::index(
            StoredDocumentCheckStatusReferenceType::findAll(['archive' => false]),
            function (StoredDocumentCheckStatusReferenceType $setting) {
                return $setting->id;
            }
        );

        if (Yii::$app->request->isPost) {
            Model::loadMultiple($settings, Yii::$app->request->post());
            foreach ($settings as $setting) {
                

                if (!$setting->save()) {
                    throw new RecordNotValid($setting);
                }
            }
        }

        return $this->render(
            'document-check-status-aliases',
            ['settings' => $settings]
        );
    }
}
