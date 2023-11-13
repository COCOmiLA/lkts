<?php

namespace common\components;

use backend\models\MasterSystemManagerInterfaceSetting;
use backend\models\PortalManagerInterfaceSetting;
use common\components\ApplicationSendHandler\BaseApplicationSendHandler;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\CodeSettingsManager\exceptions\CodeNotFilledException;
use common\models\DebuggingSoap;
use common\models\EmptyCheck;
use common\models\settings\AuthSetting;
use common\models\settings\CodeSetting;
use common\models\settings\LinkSetting;
use common\models\settings\ParentDataSetting;
use common\models\settings\SandboxSetting;
use common\models\settings\TextSetting;
use common\modules\abiturient\models\bachelor\ApplicationType;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\UserException;

class configurationManager extends Component
{
    private $memorizedCodes = [];
    private $memorizedMasterSetting = [];
    private $memorizedTexts = [];
    private $_suspend_errors = false;
    private $memorizedPortalManagerSetting = [];
    private $memorizedParentDataSetting = [];

    







    public function suspendUnspecifiedCodesError(bool $state)
    {
        $this->_suspend_errors = $state;
    }

    public function isEmptyCodeErrorsSuspended(): bool
    {
        return $this->_suspend_errors;
    }

    public function getSandboxEnabled()
    {
        $sandbox_enabled = SandboxSetting::findOne(['name' => 'sandbox_enabled']);

        if (isset($sandbox_enabled)) {
            return (bool)$sandbox_enabled->value;
        } else {
            return false;
        }
    }

    public function getAuthByLoginEnabled()
    {
        return (bool)$this->getAuthSetting('abitcode_enabled');
    }

    public function getSignupEmailEnabled()
    {
        return (bool)$this->getAuthSetting('use_email');
    }

    public function getSignupEmailConfirm(): bool
    {
        return (bool)$this->getAuthSetting('confirm_email');
    }

    public function getSignupPasswordConfirm(): bool
    {
        return (bool)$this->getAuthSetting('confirm_password');
    }

    public function getMinimalPasswordLength(): int
    {
        $minimalPasswordLength = $this->getAuthSetting('minimal_password_length');
        return (int)($minimalPasswordLength ?? 6);
    }

    public function getPasswordMustContainNumbers(): bool
    {
        return (bool)$this->getAuthSetting('password_must_contain_numbers');
    }

    public function getPasswordMustContainCapitalLetters(): bool
    {
        return (bool)$this->getAuthSetting('password_must_contain_capital_letters');
    }

    public function getPasswordMustContainSpecialCharacters(): bool
    {
        return (bool)$this->getAuthSetting('password_must_contain_special_characters');
    }

    




    private function getAuthSetting(string $name)
    {
        $setting = AuthSetting::findOne(['name' => $name]);

        if (isset($setting)) {
            return $setting->value;
        } else {
            return null;
        }
    }

    public function getSignupEmailTokenTTL(): int
    {
        $confirmEmailTokenTtl = $this->getAuthSetting('confirm_email_token_ttl');
        if (!$confirmEmailTokenTtl) {
            throw new UserException('Не удалось найти настройку "Время действия ссылки и кода для подтверждения email."');
        }
        return (int)$confirmEmailTokenTtl;
    }

    public function getAllowRememberMe(): bool
    {
        return (bool)$this->getAuthSetting('allow_remember_me');
    }

    public function getIdentityCookieDuration(): int
    {
        if (Yii::$app->configurationManager->getAllowRememberMe()) {
            return intval($this->getAuthSetting('identity_cookie_duration'));
        }

        return 0;
    }

    public function getAllTextNames()
    {
        return TextSetting::find()
            ->select(['category', 'name', 'description', 'tooltip_description'])
            ->groupBy(['category', 'name', 'description', 'tooltip_description'])
            ->asArray()
            ->all();
    }

    public function getText($text_name, ?ApplicationType $applicationType = null)
    {
        $language = Yii::$app->language;
        $type_index = $applicationType ? $applicationType->id : 0;
        $cache_key = crc32("{$language}{$type_index}{$text_name}");
        if (isset($this->memorizedTexts[$cache_key])) {
            return $this->memorizedTexts[$cache_key];
        }
        $text = null;
        try {
            $text = TextSetting::find()
                ->andWhere([
                    'name' => $text_name,
                    'language' => $language,
                    'application_type' => array_unique([0, $type_index]),
                ])
                ->orderBy(['application_type' => SORT_DESC])
                ->one();
        } catch (Throwable $e) {
            Yii::error("Не удалось получить текст: {$e->getMessage()}", 'text_settings');
        }
        if (isset($text)) {
            $this->memorizedTexts[$cache_key] = $text->value;
            return $text->value;
        } else {
            return '';
        }
    }

    public function getLink($link_name)
    {
        $link = LinkSetting::findOne(['name' => $link_name]);
        if (isset($link) && $link->isActive()) {
            return $link;
        } else {
            return false;
        }
    }

    public function resetCodesCache()
    {
        $this->memorizedCodes = [];
    }

    public function resetTextCache()
    {
        $this->memorizedTexts = [];
    }

    









    public function getCode($code_name)
    {
        if (isset($this->memorizedCodes[$code_name])) {
            return $this->memorizedCodes[$code_name];
        }
        $code = $this->getCodeEntity($code_name);
        if (
            !$this->_suspend_errors &&
            (!$code || EmptyCheck::isEmpty($code->value)) &&
            !in_array($code_name, CodeSettingsManager::GetAllowEmptyCodes())
        ) {
            throw new CodeNotFilledException("Не заполнен код по умолчанию для настройки: \"{$code->description}\". Обратитесь к администратору.
            Для исправления ошибки необходимо перейти в интерфейсе администратора на страницу \"Настройки личного кабинета поступающего\" -> \"Коды по умолчанию\" и указать корректное соответствие для всех полей.");
        }
        $value = null;
        if (isset($code, $code->value)) {
            $value = $code->value;
        }
        $this->memorizedCodes[$code_name] = $value;
        return $value;
    }

    








    public function getCodeEntity($code_name): ?CodeSetting
    {
        $code = CodeSetting::findOne(['name' => $code_name]);
        if (isset($code)) {
            return $code;
        } else {
            if ($this->_suspend_errors) {
                return null;
            }
            throw new UserException("Не найден код по умолчанию с наименованием \"{$code_name}\"");
        }
    }

    








    public function getMasterSystemManagerSetting($settingName)
    {
        if (isset($this->memorizedMasterSetting[$settingName])) {
            return $this->memorizedMasterSetting[$settingName];
        }
        $setting = MasterSystemManagerInterfaceSetting::findOne(['name' => $settingName]);
        if (isset($setting)) {

            $value = $setting->value;

            if ($setting->type === 'bool') {
                $value = $value === '1';
            }

            $this->memorizedCodes[$settingName] = $value;
            return $setting->value;
        } else {
            throw new UserException("Не найдена настройка \"{$settingName}\" интерфейса модератора 1С");
        }
    }

    public function getAllowReturnApprovedApplicationToModerating()
    {
        return $this->getCode('allow_return_approved_application_to_sent');
    }

    public function getOrCreateDebuggingSoapModel(): DebuggingSoap
    {
        return DebuggingSoap::getInstance();
    }

    




    public function getPortalManagerSetting(string $settingName): string
    {
        if (isset($this->memorizedPortalManagerSetting[$settingName])) {
            return $this->memorizedPortalManagerSetting[$settingName];
        }

        $setting = PortalManagerInterfaceSetting::findOne(['name' => $settingName]);
        if ($setting) {
            $this->memorizedPortalManagerSetting[$settingName] = $setting->value;
            return $setting->value;
        } else {
            throw new UserException("Не найдена настройка \"{$settingName}\" интерфейса модератора портала");
        }
    }

    




    public function getParentDataSetting(string $settingName)
    {
        if (isset($this->memorizedParentDataSetting[$settingName])) {
            return $this->memorizedParentDataSetting[$settingName];
        }

        $setting = ParentDataSetting::findOne(['name' => $settingName]);

        if ($settingName === 'hide_passport_data_block') {
            $is_required = $this->getParentDataSetting('require_parent_passport_data');
            if ($is_required) {
                return false;
            }
        }

        if ($settingName === 'hide_address_data_block') {
            $is_required = $this->getParentDataSetting('require_parent_address_data');
            if ($is_required) {
                return false;
            }
        }

        if ($setting) {
            $this->memorizedParentDataSetting[$settingName] = $setting->value;
            return $setting->value;
        } else {
            throw new UserException("Не найдена настройка \"{$settingName}\"");
        }
    }

    public function fullPackageEntrantProfileAvailable(): bool
    {
        $version = Yii::$app->dictionaryManager->GetInterfaceVersion('GetEntrantProfilePackage');
        if ($version == '0.0.0.0') {
            return false;
        }
        return true;
    }
}
