<?php

namespace backend\models;

use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\PersonalData;
use common\services\messagesDeliver\email\EmailDeliverer;
use common\services\messagesDeliver\sms\SmsDeliverer;
use common\services\messagesDeliver\telegram\TelegramMessageDeliverer;
use Yii;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;

class ManagerNotificationsConfigurator extends \yii\base\Model
{
    private User $user;

    public bool $notify_about_any_application_apply = false;
    public bool $notify_about_first_application_apply = false;

    public bool $use_sms = false;
    public bool $use_email = false;
    public bool $use_telegram = false;
    public string $phone = '';
    public string $email = '';
    public string $telegram_chat_id = '';

    public function __construct(User $manager, $config = [])
    {
        parent::__construct($config);
        $this->user = $manager;
        $this->loadSettingsFromUser();
    }

    private function loadSettingsFromUser()
    {
        $this->phone = ArrayHelper::getValue($this->user, 'abiturientQuestionary.personalData.main_phone', '') ?? '';
        $this->email = ArrayHelper::getValue($this->user, 'email', '') ?? '';
    }

    public function rules()
    {
        return [
            [['notify_about_any_application_apply', 'notify_about_first_application_apply'], 'boolean'],
            [['use_sms', 'use_email', 'use_telegram'], 'boolean'],
            [['phone', 'email', 'telegram_chat_id'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'notify_about_any_application_apply' => 'Уведомлять о всех заявлениях, подаваемых в мои приёмные кампании',
            'notify_about_first_application_apply' => 'Уведомлять о первой подаче пользователем заявления, в мои приёмные кампании',
            'use_sms' => 'Включить рассылку уведомлений через SMS',
            'use_email' => 'Включить рассылку уведомлений на Email',
            'use_telegram' => 'Включить рассылку уведомлений через Telegram',
            'phone' => 'Телефон',
            'email' => 'Email',
            'telegram_chat_id' => 'Telegram chat id',
        ];
    }

    public static function getInstance(User $manager): ManagerNotificationsConfigurator
    {
        $settings = ManagerNotificationSetting::find()
            ->where(['manager_id' => $manager->id])
            ->asArray()
            ->all();
        $settings = array_column($settings, 'value', 'name');
        return new ManagerNotificationsConfigurator($manager, $settings);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $this->setSetting('notify_about_any_application_apply', $this->notify_about_any_application_apply);
        $this->setSetting('notify_about_first_application_apply', $this->notify_about_first_application_apply);
        $this->setSetting('use_sms', $this->use_sms);
        $this->setSetting('use_email', $this->use_email);
        $this->setSetting('use_telegram', $this->use_telegram);
        $this->setPhone($this->phone);
        $this->setEmail($this->email);
        $this->setSetting('telegram_chat_id', $this->telegram_chat_id);

        return true;
    }

    private function setSetting(string $name, $value): void
    {
        $setting = ManagerNotificationSetting::findOne(['manager_id' => $this->user->id, 'name' => $name]);
        if ($setting === null) {
            $setting = new ManagerNotificationSetting();
            $setting->manager_id = $this->user->id;
            $setting->name = $name;
        }
        $setting->value = $value;
        if (!$setting->save()) {
            throw new RecordNotValid($setting);
        }
    }

    private function setPhone(string $phone): void
    {
        if ($phone) {
            $questionary = $this->user->abiturientQuestionary;
            if ($questionary === null) {
                $questionary = new AbiturientQuestionary();
                $questionary->user_id = $this->user->id;
                $questionary->status = AbiturientQuestionary::STATUS_CREATED;
                $questionary->draft_status = IDraftable::DRAFT_STATUS_CREATED;
                $questionary->save(false);
            }
            $personalData = $questionary->personalData;
            if ($personalData === null) {
                $personalData = new PersonalData();
                $personalData->questionary_id = $questionary->id;
                $personalData->save(false);
            }
            $personalData->main_phone = $phone;
            if (!$personalData->save(true, ['main_phone'])) {
                throw new RecordNotValid($personalData);
            }
        }
    }

    private function setEmail(string $email): void
    {
        if ($email) {
            $this->user->email = $email;
            if (!$this->user->save(true, ['email'])) {
                throw new RecordNotValid($this->user);
            }
        }
    }

    public function getDeliverers(): array
    {
        $deliverers = [];
        if ($this->use_email) {
            $deliverers[] = Yii::createObject(EmailDeliverer::class)->setRecipient($this->user);
        }
        if ($this->use_sms) {
            try {
                $deliverers[] = Yii::createObject(SmsDeliverer::class)->setRecipient($this->user);
            } catch (NotInstantiableException $e) {
                
                Yii::error('В портале не настроен SMS-шлюз', 'notifyAboutSendApplicationToCommission');
            }
        }
        if ($this->use_telegram) {
            $deliverers[] = Yii::createObject(TelegramMessageDeliverer::class)->setRecipient($this->user);
        }
        return $deliverers;
    }
}