<?php

namespace frontend\modules\user\models;

use common\components\AttachmentManager;
use common\components\attachmentSaveHandler\QuestionaryAttachmentSaveHandler;
use common\components\changeHistoryHandler\QuestionaryActiveRecordChangeHistoryHandler;
use common\components\PageRelationManager;
use common\components\RegularExpressionPasswordManager\RegularExpressionPasswordManager;
use common\components\RegulationManager;
use common\components\RegulationRelationManager;
use common\components\UserEmailConfirmTokenManager;
use common\models\Attachment;
use common\models\attachment\attachmentCollection\ActiveFormAttachmentCollection;
use common\models\AttachmentType;
use common\models\Recaptcha;
use common\models\settings\AuthSetting;
use common\models\User;
use common\models\UserRegulation;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\models\PersonalData;
use common\modules\abiturient\models\repositories\FileRepository;
use common\modules\abiturient\models\repositories\RegulationRepository;
use Throwable;
use Yii;
use yii\base\Model;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;







class AbiturientSignupForm extends Model
{
    public $email;
    public $password;
    public $username;
    public $confirm_email;
    public $confirm_password;
    public $contragent;

    public $reCaptcha;

    
    public $passportData;
    public $country_id;
    public $lastname;
    public $firstname;
    public $middlename;
    public $birthday;

    
    public $attachments;

    
    public $regulations;

    
    private $user;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->initializeUser();

        $attachments = $this->getAttachmentsToSave();

        FileRepository::SortCollection($attachments);

        $regulations_to_add = RegulationRepository::GetNotExistingRegulationsForEntity(RegulationRelationManager::RELATED_ENTITY_REGISTRATION, []);
        $regulations = [];
        foreach ($regulations_to_add as $regulation) {
            $userRegulation = new UserRegulation();
            $userRegulation->regulation_id = $regulation->id;
            $userRegulation->setRawOwner($this->user);
            $regulations[] = $userRegulation;
            if ($userRegulation->regulation->attachment_type !== null && !$userRegulation->getAttachments()->exists()) {
                $regulationAttachment = new Attachment();
                $regulationAttachment->attachment_type_id = $regulation->attachment_type;
                $userRegulation->setRawAttachment($regulationAttachment);
            }
        }

        ArrayHelper::multisort($regulations, 'regulation_id', SORT_ASC, SORT_NUMERIC);
        $regulations = ArrayHelper::index($regulations, 'regulation_id');
        $this->regulations = $regulations;
        $this->attachments = $attachments;
    }

    


    public function rules()
    {
        $minimalPasswordLength = Yii::$app->configurationManager->getMinimalPasswordLength();

        $passwordMustContainNumbers = Yii::$app->configurationManager->getPasswordMustContainNumbers();
        $passwordMustContainCapitalLetters = Yii::$app->configurationManager->getPasswordMustContainCapitalLetters();
        $passwordMustContainSpecialCharacters = Yii::$app->configurationManager->getPasswordMustContainSpecialCharacters();

        $signupPasswordConfirm = Yii::$app->configurationManager->getSignupPasswordConfirm();

        $rules = [
            [
                'username',
                'filter',
                'filter' => 'trim'
            ],
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t(
                    'sign-in/abiturient-signup/form',
                    'Подсказка с ошибкой для поля "username" на форме регистрации: `Это имя пользователя уже занято`'
                )
            ],
            [
                'username',
                'string',
                'min' => 2,
                'max' => 255
            ],

            [
                'email',
                'filter',
                'filter' => 'trim'
            ],
            [
                'email',
                'email'
            ],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t(
                    'sign-in/abiturient-signup/form',
                    'Подсказка с ошибкой для поля "email" на форме регистрации: `Этот адрес электронной почты уже занят. <a href="{url}">Восстановить пароль.</a>`',
                    ['url' => Url::to('/user/sign-in/request-password-reset')]
                )
            ],
            [
                'password',
                'required'
            ],
            [
                'password',
                'string',
                'min' => $minimalPasswordLength
            ],
            [
                [
                    'firstname',
                    'lastname',
                    'birthday',
                    'country_id',
                    'username',
                    'email',
                    'firstname'
                ],
                'required',
                'whenClient' => 'function(){return true}'
            ],
            [
                [
                    'firstname',
                    'lastname',
                    'middlename',
                    'birthday'
                ],
                'string',
                'max' => 255
            ],
            [
                'birthday',
                'date',
                'max' => PersonalData::getMaxBirthdateForValidator(),
                'tooBig' => Yii::t(
                    'sign-in/abiturient-signup/form',
                    'Подсказка с ошибкой для поля "birthday" на форме регистрации: `Минимальный возраст поступающего при регистрации: {max}`'
                ),
                'maxString' => \Yii::$app->configurationManager->getCode('min_age'),
                'format' => 'd.m.Y'
            ]
        ];

        if (Yii::$app->configurationManager->getCode('confirm-email') === '1') {
            $rules[] = [
                'confirm_email',
                'email',
            ];
            $rules[] = [
                'confirm_email',
                'compare',
                'compareAttribute' => 'email',
                'message' => Yii::t(
                    'sign-in/abiturient-signup/form',
                    'Подсказка с ошибкой для поля "confirm_email" на форме регистрации: `Электронные адреса не совпадают`'
                ),
            ];
            $rules[] = [
                'confirm_email',
                'required',
            ];
        }

        if ($signupPasswordConfirm) {
            $rules[] = [
                'confirm_password',
                'string',
                'min' => $minimalPasswordLength
            ];
            $rules[] = [
                'confirm_password',
                'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t(
                    'sign-in/abiturient-signup/form',
                    'Подсказка с ошибкой для поля "confirm_password" на форме регистрации: `Пароли не совпадают`'
                ),
            ];
            $rules[] = [
                'confirm_password',
                'required'
            ];
        }

        if (
            $passwordMustContainNumbers ||
            $passwordMustContainCapitalLetters ||
            $passwordMustContainSpecialCharacters
        ) {
            [
                
                'charList' => $charList,
                
                'matchPattern' => $matchPattern,
            ] = RegularExpressionPasswordManager::buildRegex(
                $passwordMustContainNumbers,
                $passwordMustContainCapitalLetters,
                $passwordMustContainSpecialCharacters
            );

            $message = Yii::t(
                'sign-in/abiturient-signup/form',
                'Подсказка с ошибкой поле "password" не удовлетворяет минимальным требованием символов на форме регистрации: `Пароль должен содержать: {charList}`',
                ['charList' => implode(', ', $charList)]
            );
            $rules[] = [
                'password', 'match', 'pattern' => $matchPattern,
                'message' => $message,
            ];
            if ($signupPasswordConfirm) {
                $rules[] = [
                    'confirm_password', 'match', 'pattern' => $matchPattern,
                    'message' => $message,
                ];
            }
        }

        $canNotInputLatinFio = AuthSetting::findOne(['name' => 'can_not_input_latin_fio']);
        if ($canNotInputLatinFio && !empty($canNotInputLatinFio->value)) {
            $rules[] = [['firstname', 'lastname', 'middlename'], 'match', 'pattern' => '/^[^a-zA-Z]+$/i'];
        }

        $validator = Recaptcha::getValidationArrayByName('signup');
        if (!empty($validator)) {
            $rules[] = $validator;
        }

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'email' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "email" на форме регистрации: `E-mail`'),
            'birthday' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "birthday" на форме регистрации: `Дата рождения`'),
            'lastname' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "lastname" на форме регистрации: `Фамилия`'),
            'password' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "password" на форме регистрации: `Пароль`'),
            'username' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "username" на форме регистрации: `Имя пользователя`'),
            'firstname' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "firstname" на форме регистрации: `Имя`'),
            'middlename' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "middlename" на форме регистрации: `Отчество`'),
            'country_id' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "country_id" на форме регистрации: `Гражданство`'),
            'confirm_email' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "confirm_email" на форме регистрации: `Повторите E-mail`'),
            'confirm_password' => Yii::t('sign-in/abiturient-signup/form', 'Подпись поля "confirm_password" на форме регистрации: `Повторите пароль`'),
        ];
    }

    






    public function signup()
    {
        $this->username = $this->email;
        
        if ($this->validate() && $this->passportData->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            if (!isset($transaction)) {
                throw new UserException(
                    Yii::t(
                        'sign-in/abiturient-signup/form',
                        'Сообщение об ошибке на форме авторизации: `Ошибка создания транзакции`'
                    )
                );
            }
            try {
                $user = $this->user;
                $user->username = $this->username;
                $user->email = $this->email;
                $user->setPassword($this->password);
                if ($user->save()) {

                    if (Yii::$app->configurationManager->getSignupEmailConfirm()) {
                        $token = UserEmailConfirmTokenManager::createUserEmailConfirmToken($user);
                        Yii::$app->notifier->notifyAboutEmailConfirmation($user, $token);
                    } else {
                        
                        $user->addUserRegistrationConfirm();
                    }

                    
                    $user->afterAbitSignup([
                        'passportData' => $this->passportData,
                        'lastname' => $this->lastname,
                        'firstname' => $this->firstname,
                        'middlename' => $this->middlename,
                        'birthday' => $this->birthday,
                        'password' => $this->password,
                        'country_id' => $this->country_id,
                        'contragent' => $this->contragent
                    ]);

                    foreach ($this->regulations as $regulation) {
                        $regulation->owner_id = $user->id;
                        $regulation->setChangeHistoryHandler(new QuestionaryActiveRecordChangeHistoryHandler($regulation));
                        $regulation->getChangeHistoryHandler()->setInitiator($user);
                    }

                    
                    foreach ($this->attachments as $attachment) {
                        $attachment->setAttachmentSaveHandler(new QuestionaryAttachmentSaveHandler($attachment, $user->abiturientQuestionary));
                        $attachment->getAttachmentSaveHandler()->setHistoryInitiator($user);
                    }

                    RegulationManager::handleRegulations($this->regulations, Yii::$app->request);
                    AttachmentManager::handleAttachmentUpload($this->attachments, $this->regulations);

                    $transaction->commit();

                    return $user;
                }
                $transaction->rollBack();

            } catch (Throwable $e) {
                $transaction->rollBack();
                Yii::error("Ошибка при регистрации поступающего: {$e->getMessage()}");
                throw $e;
            }
        }

        return null;
    }

    public function checkUnique()
    {
        return Yii::$app->authentication1CManager->checkAbiturientRegistration(
            $this->passportData->number,
            $this->passportData->series,
            $this->lastname,
            $this->firstname,
            $this->middlename,
            $this->email,
            $this->birthday
        );
    }

    public function getAttachmentTypes(): ActiveQuery
    {
        return AttachmentType::GetCommonAttachmentTypesQuery(PageRelationManager::RELATED_ENTITY_REGISTRATION);
    }

    


    public function getAttachmentsToSave()
    {
        $res = [];
        foreach ($this->getAttachmentTypes()->all() as $attachmentType) {
            $res[$attachmentType->id] = new ActiveFormAttachmentCollection($attachmentType, $this->user);
        }
        return $res;
    }

    private function initializeUser()
    {
        $this->user = new User();
    }
}
