<?php

namespace common\models;

use backend\models\ManageAC;
use backend\models\RBACAuthAssignment;
use backend\models\RBACAuthItem;
use cheatsheet\Time;
use common\commands\command\AddToTimelineCommand;
use common\components\EntrantModeratorManager\EntrantModeratorManager;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerValidationException;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerWrongClassException;
use common\components\EntrantModeratorManager\interfaces\IEntrantManager;
use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\RegulationRelationManager;
use common\components\soapException;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\components\UUIDManager;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\errors\RecordNotValid;
use common\models\repositories\UserRegulationRepository;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\ApplicationResubmitPermission;
use common\modules\abiturient\models\bachelor\AdmissionAgreementToDelete;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\ApplicationTypeSettings;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\CampaignInfo;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\chat\ChatUserBase;
use common\modules\abiturient\models\CommentsComing;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use filsh\yii2\oauth2server\models\OauthAccessTokens;
use stdClass;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\validators\EmailValidator;
use yii\web\IdentityInterface;






































class User extends ActiveRecord implements IdentityInterface, \OAuth2\Storage\UserCredentialsInterface, IEntrantManager
{
    use HtmlPropsEncoder;

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;

    const STATUS_ARCHIVE = 1;
    const STATUS_NOT_ARCHIVE = 0;

    const ROLE_USER = 'user';
    const ROLE_VIEWER = 'viewer';
    const ROLE_MANAGER = 'manager';
    const ROLE_ADMINISTRATOR = 'administrator';

    const ROLE_STUDENT = 'student';
    const ROLE_TEACHER = 'teacher';
    const ROLE_ABITURIENT = 'abiturient';

    const EVENT_AFTER_LOGIN = 'afterLogin';

    


    public static function tableName()
    {
        return '{{%user}}';
    }

    


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            'auth_key' => [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'auth_key'
                ],
                'value' => Yii::$app->security->generateRandomString()
            ]
        ];
    }

    


    public function scenarios()
    {
        return ArrayHelper::merge(
            parent::scenarios(),
            [
                'oauth_create' => [
                    'oauth_client',
                    'oauth_client_user_id',
                    'email',
                    'username',
                    '!status'
                ]
            ]
        );
    }


    


    public function rules()
    {
        return [
            [
                ['email'],
                'unique'
            ],
            [
                'status',
                'default',
                'value' => self::STATUS_ACTIVE
            ],
            [
                'status',
                'in',
                'range' => [
                    self::STATUS_ACTIVE,
                    self::STATUS_DELETED
                ]
            ],
            [
                ['username'],
                'filter',
                'filter' => '\yii\helpers\Html::encode'
            ],
            [
                'email',
                'required',
                'except' => ['default']
            ],
            [
                'email',
                'email'
            ],
            [
                'system_uuid',
                'string',
                'max' => 36
            ],
            [
                ['is_archive'],
                'boolean'
            ],
            [
                ['user_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredUserReferenceType::class, 'targetAttribute' => ['user_ref_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'email' => Yii::t('abiturient/user', 'Подпись для поля "email" формы "Пользователь": `E-mail`'),
            'status' => Yii::t('abiturient/user', 'Подпись для поля "status" формы "Пользователь": `Статус`'),
            'username' => Yii::t('abiturient/user', 'Подпись для поля "username" формы "Пользователь": `Имя пользователя`'),
            'logged_at' => Yii::t('abiturient/user', 'Подпись для поля "logged_at" формы "Пользователь": `Последний вход`'),
            'created_at' => Yii::t('abiturient/user', 'Подпись для поля "created_at" формы "Пользователь": `Создано`'),
            'updated_at' => Yii::t('abiturient/user', 'Подпись для поля "updated_at" формы "Пользователь": `Последнее обновление`'),
            'role' => Yii::t('abiturient/user', 'Подпись для поля "role" формы "Пользователь": `Роль`'),
        ];
    }

    


    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    


    public function getRbacAuthAssignment()
    {
        return $this->hasOne(RBACAuthAssignment::class, ['user_id' => 'id']);
    }


    public function getUserRef()
    {
        if (!$this->user_ref_id && $this->guid) {
            $userReference = UserReferenceTypeManager::getUserReferenceFrom1C($this);
            if (isset($userReference)) {
                $this->user_ref_id = $userReference->id;
                $this->save(true, ['user_ref_id']);
            }
        }
        return $this->hasOne(StoredUserReferenceType::class, ['id' => 'user_ref_id']);
    }

    public function getRegistrationNumbers()
    {
        return $this->hasMany(RegistrationNumber::class, ['user_id' => 'id']);
    }

    public function getUserRegulations()
    {
        return $this->hasMany(UserRegulation::class, ['owner_id' => 'id']);
    }

    



    public function getCleanUserRegulations()
    {
        return $this->getUserRegulations()->andOnCondition([
            'application_id' => null,
            'abiturient_questionary_id' => null,
        ]);
    }

    public function getRegnumbersString()
    {
        $str = '';
        foreach ($this->registrationNumbers as $number) {
            $str .= $number->registration_number . ', ';
        }
        return rtrim(trim((string)$str), ",");
    }

    


    public function getRawAbiturientQuestionaries()
    {
        return $this->hasMany(AbiturientQuestionary::class, ['user_id' => 'id']);
    }

    


    public function getRawAbiturientQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['user_id' => 'id']);
    }

    


    public function getAllAbiturientQuestionaries()
    {
        return $this->getRawAbiturientQuestionaries()->active();
    }

    


    public function getAbiturientQuestionary()
    {
        return $this->getRawAbiturientQuestionary()
            ->active()
            ->andWhere([AbiturientQuestionary::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_CREATED]);
    }

    


    public function getActualAbiturientQuestionary()
    {
        return $this->getRawAbiturientQuestionary()
            ->active()
            ->andWhere([AbiturientQuestionary::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_APPROVED]);
    }

    


    public function getAdmissionAgreementToDelete()
    {
        return $this->hasMany(AdmissionAgreementToDelete::class, ['user_id' => 'id']);
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['owner_id' => 'id']);
    }

    public function getRawApplications()
    {
        return $this->hasMany(BachelorApplication::class, ['user_id' => 'id']);
    }

    public function getApplications()
    {
        return $this->getRawApplications()
            ->active()
            ->joinWith('type.campaign'); 
    }

    public function resetApplicationStatuses()
    {
        foreach ($this->getApplications()->andWhere([BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_CREATED])->all() as $application) {
            $application->resetStatus();
        }
    }

    


    private function getAvailableApplicationTypesQuery(): ActiveQuery
    {
        $date = date('Y-m-d H:i:s');
        $tnCampaignInfo = CampaignInfo::tableName();
        $tnApplicationType = ApplicationType::tableName();
        $tnAdmissionCampaign = AdmissionCampaign::tableName();
        $tnApplicationTypeSettings = ApplicationTypeSettings::tableName();
        $createdAppTypeIds = $this->getApplications()
            ->andWhere(['not', [BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_MODERATING]])
            ->select([ApplicationType::tableName() . '.id'])->distinct();
        $query = ApplicationType::find()
            ->select([
                "{$tnApplicationType}.id",
                "{$tnApplicationType}.name",
                "{$tnApplicationType}.campaign_id",
                "{$tnApplicationType}.blocked",
                "SUM(CASE WHEN {$tnApplicationTypeSettings}.name = 'disable_type' THEN {$tnApplicationTypeSettings}.value ELSE 0 END) new_disable_type",
            ])
            ->active()
            ->joinWith(['applicationTypeSettings'])
            ->groupBy(["{$tnApplicationType}.id", "{$tnApplicationType}.name", "{$tnApplicationType}.campaign_id"]);

        $availableApplicationTypesQuery = (new Query())
            ->select(['new_app_type.id'])
            ->from(['new_app_type' => $query])
            ->leftJoin($tnAdmissionCampaign, "new_app_type.campaign_id = {$tnAdmissionCampaign}.id")
            ->leftJoin($tnCampaignInfo, "{$tnAdmissionCampaign}.id = {$tnCampaignInfo}.campaign_id")
            ->andWhere(['new_app_type.blocked' => false])
            ->andWhere(['not', ["{$tnAdmissionCampaign}.archive" => true]])
            ->andWhere(['not', ['new_app_type.new_disable_type' => 1]])
            ->andWhere(['<', IndependentQueryManager::strToDateTime("{$tnCampaignInfo}.date_start"), $date])
            ->andWhere(['>=', IndependentQueryManager::strToDateTime("{$tnCampaignInfo}.date_final"), $date])
            ->andWhere(['not', ["{$tnCampaignInfo}.archive" => true]])
            ->andWhere(['not', ['new_app_type.id' => $createdAppTypeIds]])
            ->groupBy('new_app_type.id');

        return ApplicationType::find()
            ->where([
                'IN',
                "{$tnApplicationType}.id",
                $availableApplicationTypesQuery
            ]);
    }

    


    public function getAvailableApplicationTypes(): array
    {
        return $this->getAvailableApplicationTypesQuery()->all();
    }

    public function haveBlockedTypes()
    {
        $types = ApplicationType::find()->active()->all();
        $haveBlocked = false;
        foreach ($types as $type) {
            if ($type->stageTwoStarted() || $type->blocked) {
                $haveBlocked = true;
                break;
            }
        }
        return $haveBlocked;
    }

    public function getIndividualAchievements()
    {
        return $this->hasMany(IndividualAchievement::class, ['user_id' => 'id']);
    }

    


    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    


    public static function findIdentityByAccessToken($token, $type = null)
    {
        $oauthAccess = OauthAccessTokens::findOne(['access_token' => $token]);
        if ($oauthAccess != null) {
            return static::findActive()->andWhere(['id' => $oauthAccess->user_id, 'status' => self::STATUS_ACTIVE])->one();
        } else {
            return false;
        }
    }

    





    public static function findByUsername($username)
    {
        return static::findActive()
            ->andWhere(new Expression("BINARY [[username]] = :username", [':username' => $username]))
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->limit(1)
            ->one();
    }

    





    public static function findByPasswordResetToken($token)
    {
        $expire = Time::SECONDS_IN_A_DAY;
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        if ($timestamp + $expire < time()) {
            
            return null;
        }

        return static::findActive()
            ->andWhere([
                'password_reset_token' => $token,
                'status' => self::STATUS_ACTIVE
            ])
            ->one();
    }

    


    public function getId()
    {
        return $this->getPrimaryKey();
    }

    


    public function getAuthKey()
    {
        return $this->auth_key;
    }

    


    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    





    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    




    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    


    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    


    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function getRole()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->getId());
        if (isset($roles[User::ROLE_ADMINISTRATOR])) {
            return User::ROLE_ADMINISTRATOR;
        }
        if (isset($roles[User::ROLE_MANAGER])) {
            return User::ROLE_MANAGER;
        }
        if (isset($roles[User::ROLE_ABITURIENT])) {
            return User::ROLE_ABITURIENT;
        }
        if (isset($roles[User::ROLE_TEACHER])) {
            return User::ROLE_TEACHER;
        }
        if (isset($roles[User::ROLE_STUDENT])) {
            return User::ROLE_STUDENT;
        }
        return User::ROLE_USER;
    }

    public static function getRoles()
    {
        return [
            self::ROLE_USER => Yii::t('abiturient/user', 'Подпись роли "ROLE_USER" формы "Пользователь": `Пользователь`'),
            self::ROLE_MANAGER => Yii::t('abiturient/user', 'Подпись роли "ROLE_MANAGER" формы "Пользователь": `Менеджер`'),
            self::ROLE_ADMINISTRATOR => Yii::t('abiturient/user', 'Подпись роли "ROLE_ADMINISTRATOR" формы "Пользователь": `Администратор`'),
            self::ROLE_STUDENT => Yii::t('abiturient/user', 'Подпись роли "ROLE_STUDENT" формы "Пользователь": `Студент`'),
            self::ROLE_TEACHER => Yii::t('abiturient/user', 'Подпись роли "ROLE_TEACHER" формы "Пользователь": `Преподаватель`'),
            self::ROLE_ABITURIENT => Yii::t('abiturient/user', 'Подпись роли "ROLE_ABITURIENT" формы "Пользователь": `Поступающий`'),
        ];
    }

    




    public static function getStatuses($status = false)
    {
        $statuses = [
            self::STATUS_ACTIVE => Yii::t(
                'abiturient/user',
                'Подпись наличия статуса "STATUS_ACTIVE" формы "Пользователь": `Активно`'
            ),
            self::STATUS_DELETED => Yii::t(
                'abiturient/user',
                'Подпись отсутствия статуса "STATUS_DELETED" формы "Пользователь": `Удалено`'
            )
        ];
        return $status !== false ? ArrayHelper::getValue($statuses, $status) : $statuses;
    }

    




    public static function getArchives($archive = false)
    {
        $archives = [
            self::STATUS_NOT_ARCHIVE => Yii::t(
                'abiturient/user',
                'Подпись наличия статуса "STATUS_NOT_ARCHIVE" формы "Пользователь": `Нет`'
            ),
            self::STATUS_ARCHIVE => Yii::t(
                'abiturient/user',
                'Подпись отсутствия статуса "STATUS_ARCHIVE" формы "Пользователь": `Да`'
            )
        ];
        return $archive !== false ? ArrayHelper::getValue($archives, $archive) : $archives;
    }

    



    public function afterSignup(array $profileData = [])
    {
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'category' => 'user',
            'event' => 'signup',
            'data' => [
                'public_identity' => $this->getPublicIdentity(),
                'user_id' => $this->getId(),
                'created_at' => $this->created_at
            ]
        ]));
        $profile = new UserProfile();
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        $this->link('userProfile', $profile);
        
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
    }

    







    public function afterAbitSignup(array $profileData = [])
    {
        $profile = new UserProfile();
        $passportData = $profileData['passportData'];
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        $profile->passport_series = $passportData->series;
        $profile->passport_number = $passportData->number;
        $this->link('userProfile', $profile);

        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'category' => 'user',
            'event' => 'signup',
            'data' => [
                'public_identity' => $this->email,
                'user_id' => $this->getId(),
                'created_at' => $this->created_at
            ]
        ]));

        
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole(User::ROLE_ABITURIENT), $this->getId());
        $profile->initAbiturientQuestionary();
        unset($this->abiturientQuestionary);
        Yii::$app->notifier->notifyAboutRegister($this->id, $profileData['password']);
    }

    public function testConnection()
    {
        if ($this->guid != null && $this->isInRole(self::ROLE_ABITURIENT)) {
            Yii::$app->soapClientAbit->load("TestConnect", []);
        }
        return true;
    }

    public function updateActualQuestionary(): void
    {
        
        if (
            empty(BachelorApplication::GetExistingAppTypes($this))
        ) {
            DraftsManager::getActualQuestionary(
                $this,
                UserReferenceTypeManager::IsUserRefDataVersionOutdated($this)
            );
        }
    }

    







    public function assignUserRef($raw_user_ref)
    {
        $user_ref = ReferenceTypeManager::GetOrCreateReference(StoredUserReferenceType::class, $raw_user_ref);
        $this->user_ref_id = ArrayHelper::getValue($user_ref, 'id');
        $this->guid = ArrayHelper::getValue($user_ref, 'reference_id');
        if (!$this->save(false, ['user_ref_id', 'guid'])) {
            throw new RecordNotValid($this);
        }
        unset($this->userRef);
    }

    public function updateUserRefDataVersion(): void
    {
        if ($this->userRef) {
            $abit_ref = UserReferenceTypeManager::getRawUserReferenceFrom1CByGuid($this->userRef->reference_uid, 'Идентификатор');
            $this->assignUserRef($abit_ref);
        }
    }

    public function hasAppInOneS(ApplicationType $type): bool
    {
        if (!$this->userRef) {
            return false;
        }
        $neededTypes = [];
        try {
            $neededTypes = BachelorApplication::GetExistingAppTypes($this);
        } catch (\Throwable $e) {
            return false;
        }

        
        return boolval(array_filter($neededTypes, function (ApplicationType $t) use ($type) {
            return $t->id == $type->id;
        }));
    }

    public function updateAllApplicationsFrom1C()
    {
        if ($this->userRef) {
            try {
                $neededTypes = BachelorApplication::GetExistingAppTypes($this);
                $touched_actual_app_ids = [];

                foreach ($neededTypes as $type) {
                    $application = DraftsManager::getActualApplication($this, $type);
                    if ($application) {
                        $touched_actual_app_ids[] = $application->id;
                    }
                }
                
                
                $actual_apps_to_delete = $this->getApplications()
                    ->andWhere(['not', [BachelorApplication::tableName() . '.id' => $touched_actual_app_ids]])
                    ->andWhere([BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_APPROVED])
                    ->all();
                foreach ($actual_apps_to_delete as $actual_app_to_delete) {
                    $actual_app_to_delete->archive();
                }
            } catch (soapException $e) {
                Yii::error($e->getMessage(), 'abitAuth');
            }
        }
    }

    public function syncApplicationsAndQuestionaryWith1C()
    {
        if ($this->userRef && $this->isInRole(self::ROLE_ABITURIENT)) {
            
            $this->updateAllApplicationsFrom1C();
            
            $this->updateActualQuestionary();
        }

        return true;
    }

    public function isModer(): bool
    {
        return $this->isInRole(User::ROLE_MANAGER);
    }

    public function isAdmin(): bool
    {
        return $this->isInRole(User::ROLE_ADMINISTRATOR);
    }

    public function isAbiturient(): bool
    {
        return $this->isInRole(User::ROLE_ABITURIENT);
    }

    public function isTransfer(): bool
    {
        return Yii::$app->session->has('transfer');
    }

    public function isViewer(): bool
    {
        return $this->isInRole(User::ROLE_VIEWER);
    }

    public function getTransferUser(): ?User
    {
        if ($this->isTransfer()) {
            return User::findOne(Yii::$app->session->get('transfer'));
        }
        return null;
    }

    public function isInternalRole(): bool
    {
        return $this->isInRole(User::ROLE_ADMINISTRATOR)
            || $this->isInRole(User::ROLE_MANAGER)
            || $this->isInRole(User::ROLE_TEACHER)
            || $this->isInRole(User::ROLE_VIEWER);
    }

    public function getFullName(): string
    {
        return $this->getPublicIdentity();
    }

    


    public function getPublicIdentity()
    {
        if ($this->userProfile && $this->userProfile->getFullName()) {
            if ($this->abiturientQuestionary != null && $this->abiturientQuestionary->personalData != null) {
                return Html::encode($this->abiturientQuestionary->personalData->getFullname());
            }
            return Html::encode($this->userProfile->getFullName());
        }
        if ($this->username) {
            return Html::encode($this->username);
        }
        return Html::encode($this->email);
    }

    


    public function getAbsFullName()
    {
        if ($this->userProfile && $this->userProfile->getAbsFullName()) {
            if (
                $this->abiturientQuestionary != null &&
                $this->abiturientQuestionary->personalData != null &&
                $absFio = Html::encode(trim($this->abiturientQuestionary->personalData->getAbsFullName()))
            ) {
                return $absFio;
            }

            if ($absFio = Html::encode(trim($this->userProfile->getAbsFullName()))) {
                return $absFio;
            }
        }
        if ($this->username) {
            return Html::encode($this->username);
        }
        return Html::encode($this->email);
    }

    public function checkUserCredentials($username, $password)
    {
        $user = User::findByUsername($username);

        return ($user != null && $user->validatePassword($password));
    }

    public function getUserDetails($username)
    {
        $user = User::findByUsername($username);
        if ($user != null) {
            return [
                "user_id" => $user->id,
                "scope" => ""
            ];
        } else {
            return false;
        }
    }

    public function isInRole($role)
    {
        if (!$role) {
            return false;
        }
        $user_roles = \Yii::$app->authManager->getRolesByUser($this->id);
        if (array_key_exists($role, $user_roles)) {
            return true;
        } else {
            return false;
        }
    }

    public static function mapRole($role)
    {
        switch ($role) {
            case ('Student'):
                return self::ROLE_STUDENT;
            case ('Teacher'):
                return self::ROLE_TEACHER;
            case ('Abiturient'):
                return self::ROLE_ABITURIENT;
            default:
                return self::ROLE_USER;
        }
    }

    public static function getAllStudentSideRole()
    {
        $rolesList = [];
        $path = Yii::getAlias('@common') . FileHelper::normalizePath('/modules/student/controllers');
        $controllers = scandir($path);
        if (!empty($controllers)) {
            foreach ($controllers as $controller) {
                $splitName = explode('Controller.php', $controller);
                if (!empty($splitName) && count($splitName) > 1) {
                    $rolesList[self::mapRole($splitName[0])] = self::getRoleName($splitName[0]);
                }
            }
        }
        return $rolesList;
    }

    


    public static function getAllRole()
    {
        $roles = RBACAuthItem::findAll(['type' => 1]);
        if (empty($roles)) {
            return [];
        }
        return array_map(
            function ($role) {
                
                return $role->name;
            },
            $roles
        );
    }

    




    public static function getRoleTranslatedName($role = '')
    {
        switch ($role) {
            case self::ROLE_USER:
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_USER" формы "Пользователь": `Пользователь`'
                );

            case self::ROLE_MANAGER:
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_MANAGER" формы "Пользователь": `Менеджер`'
                );

            case self::ROLE_ADMINISTRATOR:
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_ADMINISTRATOR" формы "Пользователь": `Администратор`'
                );

            case self::ROLE_STUDENT:
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_STUDENT" формы "Пользователь": `Студент`'
                );

            case self::ROLE_TEACHER:
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_TEACHER" формы "Пользователь": `Преподаватель`'
                );

            case self::ROLE_ABITURIENT:
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_ABITURIENT" формы "Пользователь": `Поступающий`'
                );
        }

        if (is_string($role) && $role) {
            return $role;
        }

        return '-';
    }

    public static function getRoleName($role)
    {
        switch ($role) {
            case ('Student'):
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_STUDENT" формы "Пользователь": `Студент`'
                );

            case ('Teacher'):
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_TEACHER" формы "Пользователь": `Преподаватель`'
                );

            case ('Abiturient'):
                return Yii::t(
                    'abiturient/user',
                    'Подпись роли "ROLE_ABITURIENT" формы "Пользователь": `Поступающий`'
                );

            default:
                return Yii::t(
                    'abiturient/user',
                    'Подпись пользователя без роли формы "Пользователь": `Пользователь`'
                );
        }
    }

    public function canMakeStepRoute(string $step, ?BachelorApplication $application = null): bool
    {
        if (!$this->isInRole(self::ROLE_ABITURIENT)) {
            return false;
        }

        $haveApp = false;
        $haveSpec = false;
        $specs_filled = false;
        $haveEge = false;
        $filled_education = false;
        $app_in_send_mode = false;
        $questionary = null;
        $campaign_ref_uid = null;
        if ($application != null) {
            $questionary = $application->abiturientQuestionary;
            $app_in_send_mode = $application->isNotCreatedDraft(); 
            $haveApp = true;
            $filled_education = $app_in_send_mode || !empty($application->educations);
            $specialities = $application->getSpecialities()->all();
            $specs_filled = $app_in_send_mode;
            $haveSpec = $app_in_send_mode;
            if (!$app_in_send_mode) {
                if (!empty($specialities)) {
                    $haveSpec = true;
                    $specs_filled = true;
                    
                    foreach ($specialities as $speciality) {
                        $speciality->scenario = BachelorSpeciality::SCENARIO_FULL_VALIDATION;
                        if (!$speciality->validate()) {
                            $specs_filled = false;
                            break; 
                        }
                    }
                }
            }
            $haveEge = $app_in_send_mode || ($application->validateUnstagedDisciplineResults() && $application->validateUnstagedDisciplineSets());

            $campaign_ref_uid = ArrayHelper::getValue($application, 'type.campaign.referenceType.reference_uid');
        } else {
            $questionary = $this->abiturientQuestionary;
        }

        switch ($step) {
            case 'my-applications':
                return $this->getApplications()->exists();
            case 'questionary':
                return true;
            case 'make-application':
                if ($app_in_send_mode) {
                    return true;
                }
                if (!$questionary) {
                    return false;
                }

                return $questionary->status != AbiturientQuestionary::STATUS_CREATED
                    && $questionary->isPassportsRequiredFilesAttached()
                    && $questionary->isRequiredCommonFilesAttached();

            case 'ege-result':
                if ($app_in_send_mode) {
                    return true;
                }
                return $this->canMakeStepRoute('specialities', $application) && $haveSpec && $specs_filled;
            case 'printforms':
            case 'education':
                if ($app_in_send_mode) {
                    return true;
                }
                if ($questionary && $haveApp && $questionary->isRequiredCommonFilesAttached()) {
                    return true;
                }
                return false;
            case 'accounting-benefits':
                if ($app_in_send_mode) {
                    return true;
                }
                return $this->canMakeStepRoute('education', $application) && $filled_education;
            case 'specialities':
                if ($app_in_send_mode) {
                    return true;
                }
                if ($questionary && $haveApp && $filled_education) {
                    $all_required_edu_attachments = !$application->getNotFilledRequiredEducationScanTypeIds();
                    return
                        $all_required_edu_attachments
                        && $questionary->isRequiredCommonFilesAttached()
                        && $questionary->isPassportsRequiredFilesAttached();
                }
                return false;
            case 'ia-list':
                if ($app_in_send_mode) {
                    return true;
                }
                return $this->canMakeStepRoute('ege-result', $application) && $haveEge;
            case 'load-scans':
                if ($app_in_send_mode) {
                    return true;
                }
                return $application && !$application->type->hide_scans_page && $this->canMakeStepRoute('ia-list', $application);
            case 'make-comment':
                if ($app_in_send_mode) {
                    return true;
                }
                return $this->canMakeStepRoute('ia-list', $application);
            case 'send-application':

                return !$app_in_send_mode
                    && $this->canMakeStepRoute('ia-list', $application)
                    && !Attachment::getNotFilledRequiredAttachmentTypeIds(
                        $questionary->getAttachments()->with(['attachmentType'])->all(),
                        AttachmentType::GetRequiredCommonAttachmentTypeIds(
                            [
                                AttachmentType::RELATED_ENTITY_REGISTRATION,
                                AttachmentType::RELATED_ENTITY_QUESTIONARY
                            ],
                            $campaign_ref_uid
                        )
                    );

            default:
                return false;
        }
    }

    public function canViewStep(string $step, ?BachelorApplication $application = null): bool
    {
        if (!$this->isInRole(self::ROLE_ABITURIENT)) {
            return false;
        }

        $haveApp = boolval($application);

        switch ($step) {
            case 'my-applications':
                $result = $this->getApplications()->exists();
                break;
            case 'questionary':
                $result = true;
                break;
            case 'make-application':
                $result = $this->getAvailableApplicationTypesQuery()->exists();
                break;
            case 'ege-result':
                if (isset($application->type->hide_ege)) {
                    $show_ege = !$application->type->hide_ege;
                } else { 
                    $show_ege = true;
                }

                $result = $haveApp && $show_ege;
                break;
            case 'dormitory':
            case 'printforms':
            case 'specialities':
            case 'make-comment':
            case 'education':
                $result = $haveApp;
                break;
            case 'load-scans':
                $result = $haveApp && !$application->type->hide_scans_page;
                break;
            case 'send-application':
                $result = $haveApp && !$application->isNotCreatedDraft();
                break;
            case 'ia-list':
                if (isset($application) && isset($application->type)) {
                    $result = !$application->type->hide_ind_ach;
                } else { 
                    $result = true;
                }
                break;
            case 'accounting-benefits':
                $result = $this->canViewStep('education', $application);
                if ($result) {
                    $hideBenefitsBlock = ArrayHelper::getValue($application, 'type.hide_benefits_block', false);
                    $hideOlympicBlock = ArrayHelper::getValue($application, 'type.hide_olympic_block', false);
                    $hideTargetsBlock = ArrayHelper::getValue($application, 'type.hide_targets_block', false);
                    $result = !$hideBenefitsBlock || !$hideOlympicBlock || !$hideTargetsBlock;
                }
                break;
            default:
                $result = false;
                break;
        }
        return $result;
    }

    public function canMakeStep(string $step, ?BachelorApplication $application = null): bool
    {
        return $this->canMakeStepRoute($step, $application) && $this->canMakeStepRegulation($step, $application);
    }

    public function canMakeStepRegulation(string $step, ?BachelorApplication $application = null, $write_flash = true): bool
    {
        if (!$this->isInRole(self::ROLE_ABITURIENT)) {
            return false;
        }

        $relatedEntities = $this->getStepperRegulationRelatedList($step);

        $questionary = $application ? $application->abiturientQuestionary : $this->abiturientQuestionary;

        
        if ($questionary && ($questionary->isArchive() || !$questionary->canEditQuestionary() || AbiturientQuestionary::isBlockedAfterApprove($questionary))) {
            $relatedEntities = array_filter($relatedEntities, function ($el) {
                return $el !== RegulationRelationManager::RELATED_ENTITY_QUESTIONARY;
            });
        }

        if ($relatedEntities && (!$application || !$application->hasApprovedApplication())) {
            $regulationCheck = UserRegulationRepository::CheckRequiredRegulations($questionary, $relatedEntities, $application);
            $regulationFileCheck = !UserRegulationRepository::GetRegulationsWithEmptyFile($questionary, $relatedEntities, $application);
            $allow = $regulationCheck && $regulationFileCheck;
            if (!$allow && $write_flash) {
                \Yii::$app->session->setFlash('regulationError', Yii::t(
                    'abiturient/header/all',
                    'Текст алерта о невозможности подачи заявления на панели навигации ЛК: `Прикреплены не все обязательные скан-копии нормативных документов или отсутствует подтверждение ознакомления с документом`'
                ), false);
            }
            return $allow;
        }

        return true;
    }

    public function getStepperRegulationRelatedList($step): array
    {
        switch ($step) {
            case 'make-application':
                return [RegulationRelationManager::RELATED_ENTITY_QUESTIONARY];
            case 'education':
                return $this->getStepperRegulationRelatedList('make-application');
            case 'accounting-benefits':
                return array_merge($this->getStepperRegulationRelatedList('education'), [
                    RegulationRelationManager::RELATED_ENTITY_EDUCATION,
                ]);
            case 'specialities':
                return array_merge($this->getStepperRegulationRelatedList('accounting-benefits'), [
                    RegulationRelationManager::RELATED_ENTITY_TARGET_RECEPTION,
                    RegulationRelationManager::RELATED_ENTITY_OLYMPIAD,
                    RegulationRelationManager::RELATED_ENTITY_PREFERENCE
                ]);
            case 'ege-result':
                return array_merge($this->getStepperRegulationRelatedList('specialities'), [
                    RegulationRelationManager::RELATED_ENTITY_APPLICATION,
                ]);
            case 'ia-list':
                return array_merge($this->getStepperRegulationRelatedList('ege-result'), [
                    RegulationRelationManager::RELATED_ENTITY_EGE,
                ]);
            case 'load-scans':
            case 'make-comment':
            case 'send-application':
            case 'printforms':
                return $this->getStepperRegulationRelatedList('ia-list');
            default:
                return [];
        }
    }

    public function createFrom1C($password = null, $newUser = true, $forceEmail = null, &$creatingErrors = null)
    {
        $validator = new EmailValidator();

        $error = null;
        if ($validator->validate($this->username, $error)) {
            $this->email = $this->username;
        } else {
            $this->email = $forceEmail;
        }

        if (!$this->username && $this->email != 'нет' && $this->email != null) {
            $this->username = $this->email;
        }
        if ($password == null) {
            $password = self::generateRandomString(10);
        }
        $this->setPassword($password);
        if (!$this->save()) {
            throw new RecordNotValid($this);
        }
        $this->addUserRegistrationConfirm();
        $questionary = AbiturientQuestionary::find()
            ->active()
            ->andWhere(['user_id' => $this->id])
            ->one();
        if ($questionary == null) {
            $questionary = new AbiturientQuestionary();
            $questionary->user_id = $this->id;
        }
        $questionary->status = AbiturientQuestionary::STATUS_CREATE_FROM_1C;
        $questionary->draft_status = IDraftable::DRAFT_STATUS_APPROVED;

        if (!$questionary->save()) {
            throw new RecordNotValid($questionary);
        }
        
        $status = $questionary->getFrom1CWithParents(false, $creatingErrors);
        if (!$status) {
            return false;
        }
        $questionary = AbiturientQuestionary::findOne($questionary->id);
        $profile = $this->userProfile;
        if ($profile == null) {
            $profile = new UserProfile();
        }

        $profile->locale = Yii::$app->language;
        $profile->firstname = $questionary->personalData->firstname;
        $profile->middlename = $questionary->personalData->middlename;
        $profile->lastname = $questionary->personalData->lastname;
        $profile->gender = $questionary->personalData->gender;
        $profile->gender_id = $questionary->personalData->gender_id;
        $profile->birthday = $questionary->personalData->birthdate;
        $profile->passport_series = $questionary->personalData->passport_series;
        $profile->passport_number = $questionary->personalData->passport_number;
        if ($newUser) {
            $this->link('userProfile', $profile);
            $auth = Yii::$app->authManager;
            $auth->assign($auth->getRole(User::ROLE_ABITURIENT), $this->getId());
        }
        if (!$profile->save()) {
            throw new RecordNotValid($profile);
        }

        $this->syncApplicationsAndQuestionaryWith1C();
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'category' => 'user',
            'event' => 'signup',
            'data' => [
                'public_identity' => $this->getPublicIdentity(),
                'user_id' => $this->getId(),
                'created_at' => $this->created_at
            ]
        ]));
        
        Yii::$app->notifier->notifyAboutRegister($this->id, $password);
        return true;
    }

    public function setAsAbiturient()
    {
        $questionary = new AbiturientQuestionary();
        $questionary->user_id = $this->id;
        $questionary->status = AbiturientQuestionary::STATUS_APPROVED;
        $questionary->draft_status = IDraftable::DRAFT_STATUS_APPROVED;

        $questionary->save();
        $questionary->getFrom1CWithParents();
        $questionary = AbiturientQuestionary::findOne($questionary->id);

        $profile = $this->userProfile;
        if ($profile == null) {
            $profile = new UserProfile();
            $profile->locale = Yii::$app->language;
        }
        $profile->firstname = $questionary->personalData->firstname;
        $profile->middlename = $questionary->personalData->middlename;
        $profile->lastname = $questionary->personalData->lastname;
        $profile->gender = $questionary->personalData->gender;
        $profile->gender_id = $questionary->personalData->gender_id;
        $profile->birthday = $questionary->personalData->birthdate;
        $profile->passport_series = $questionary->personalData->passport_series;
        $profile->passport_number = $questionary->personalData->passport_number;

        if ($profile->isNewRecord) {
            $this->link('userProfile', $profile);
        }
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole(User::ROLE_ABITURIENT), $this->getId());
        $profile->save();
        $user = User::findOne($this->id);

        $user->save();

        return true;
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen((string)$characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    


    public static function findActive(string $alias = 'user')
    {
        return parent::find()
            ->andOnCondition(["{$alias}.is_archive" => false]);
    }

    



    public function addUserRegistrationConfirm(): UserRegistrationEmailConfirm
    {
        $this->archiveAllRegistrationConfirm();
        $confirm = new UserRegistrationEmailConfirm();
        $confirm->user_id = $this->id;
        $confirm->status = UserRegistrationEmailConfirm::STATUS_ACTIVE;
        $confirm->save();
        return $confirm;
    }

    


    public function archiveAllRegistrationConfirm()
    {
        UserRegistrationEmailConfirm::updateAll([
            'status' => UserRegistrationEmailConfirm::STATUS_DEPRECATED
        ], [
            'user_id' => $this->id
        ]);
    }

    public function getUserRegistrationEmailConfirm(): ActiveQuery
    {
        return $this->hasOne(UserRegistrationEmailConfirm::class, [
            'user_id' => 'id'
        ])->andWhere([
            'status' => UserRegistrationEmailConfirm::STATUS_ACTIVE
        ]);
    }

    



    public function isRegistrationConfirmed(): bool
    {
        return $this->userRegistrationEmailConfirm !== null;
    }

    public function getUserRegistrationConfirmToken(): ActiveQuery
    {
        return $this->hasOne(UserRegistrationConfirmToken::class, ['user_id' => 'id'])
            ->andWhere(['status' => UserRegistrationConfirmToken::STATUS_UNTOUCHED]);
    }

    


    public function getChangeHistory()
    {
        return $this->hasMany(ChangeHistory::class, ['initiator_id' => 'id']);
    }

    


    public function getCommentsComing()
    {
        return $this->hasMany(CommentsComing::class, ['author_id' => 'id']);
    }

    


    public function getEntrantManager()
    {
        return $this->hasOne(EntrantManager::class, ['local_manager' => 'id']);
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();

        $errorFrom = '';
        $deleteSuccess = true;
        try {
            Yii::$app->authManager->revokeAll($this->id);

            BachelorApplication::updateAll(['last_manager_id' => null], ['last_manager_id' => $this->id]);
            BachelorApplication::updateAll(['archived_by_user_id' => null], ['archived_by_user_id' => $this->id]);
            foreach ($this->getEntrantManager()->all() as $entrant_manager) {
                $entrant_manager->delete();
            }
            
            $userProfile = $this->userProfile;
            if (isset($userProfile)) {
                $deleteSuccess = $userProfile->delete();
            }
            if (!$deleteSuccess) {
                $errorFrom .= "{$userProfile->tableName()} -> {$userProfile->id}\n";
            }
            if ($deleteSuccess) {
                $userRegistrationConfirmTokens = UserRegistrationConfirmToken::find()
                    ->where(['user_id' => $this->id])
                    ->all();
                if (!empty($userRegistrationConfirmTokens)) {
                    foreach ($userRegistrationConfirmTokens as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $userRegistrationEmailConfirms = UserRegistrationEmailConfirm::find()
                    ->where(['user_id' => $this->id])
                    ->all();
                if (!empty($userRegistrationEmailConfirms)) {
                    foreach ($userRegistrationEmailConfirms as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $registrationNumbers = $this->registrationNumbers;
                if (!empty($registrationNumbers)) {
                    foreach ($registrationNumbers as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $userRegulations = $this->getUserRegulations()
                    ->with(['rawAttachments'])
                    ->all();
                if (!empty($userRegulations)) {
                    foreach ($userRegulations as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $admissionAgreementToDelete = $this->admissionAgreementToDelete;
                if (!empty($admissionAgreementToDelete)) {
                    foreach ($admissionAgreementToDelete as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $changeHistory = $this->getChangeHistory()->all();
                if (!empty($changeHistory)) {
                    foreach ($changeHistory as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $tnAbiturientQuestionary = AbiturientQuestionary::tableName();
                $changeHistory = ChangeHistory::find()
                    ->joinWith('questionary')
                    ->andWhere(["{$tnAbiturientQuestionary}.user_id" => $this->id])
                    ->all();
                if (!empty($changeHistory)) {
                    foreach ($changeHistory as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $tnBachelorApplication = BachelorApplication::tableName();
                $changeHistory = ChangeHistory::find()
                    ->joinWith('application')
                    ->andWhere(["{$tnBachelorApplication}.user_id" => $this->id])
                    ->all();
                if (!empty($changeHistory)) {
                    foreach ($changeHistory as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $commentsComing = $this->commentsComing;
                if (!empty($commentsComing)) {
                    foreach ($commentsComing as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                AbiturientQuestionary::updateAll(['approver_id' => null], ['approver_id' => $this->id]);
                $abiturientQuestionary = $this->rawAbiturientQuestionary;
                if (isset($abiturientQuestionary)) {
                    $deleteSuccess = $abiturientQuestionary->delete();
                }
                if (!$deleteSuccess) {
                    $errorFrom .= "{$abiturientQuestionary->tableName()} -> {$abiturientQuestionary->id}\n";
                }
            }
            if ($deleteSuccess) {
                $applications = $this->getRawApplications()->all();
                if (!empty($applications)) {
                    foreach ($applications as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $attachments = $this->attachments;
                if (!empty($attachments)) {
                    foreach ($attachments as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
            if ($deleteSuccess) {
                $chatUsers = $this->getChatUserBases()->all();
                if (!empty($chatUsers)) {
                    foreach ($chatUsers as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $deleteSuccess = false;
            $errorFrom .= "{$e->getMessage()}\n";
        }

        if ($deleteSuccess) {
            $transaction->commit();
        } else {
            Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");
            $transaction->rollBack();
        }
        return (bool)$deleteSuccess;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            
            $this->system_uuid = UUIDManager::GetUUID();
        }
        return parent::beforeSave($insert);
    }

    public function getChatUserBases()
    {
        return $this->hasMany(ChatUserBase::class, ['user_id' => 'id']);
    }

    




    public function getEntrantManagerEntity(): EntrantManager
    {
        return EntrantModeratorManager::GetOrCreateEntrantModerator($this);
    }

    public function hasApprovedApps(): bool
    {
        return (bool)array_filter($this->applications, function (BachelorApplication $app) {
            return $app->status == ApplicationInterface::STATUS_APPROVED || $app->isIn1CByModerateHistory();
        });
    }

    


    public function hasSentAppsQuery(): ActiveQuery
    {
        return $this->getApplications()
            ->andWhere([BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_SENT])
            ->andWhere([
                BachelorApplication::tableName() . '.status' => [
                    BachelorApplication::STATUS_SENT,
                    BachelorApplication::STATUS_SENT_AFTER_APPROVED,
                    BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED,
                    BachelorApplication::STATUS_WANTS_TO_BE_REMOTE,
                    BachelorApplication::STATUS_WANTS_TO_RETURN_ALL,
                    BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED,
                ]
            ]);
    }

    public function hasSentApps(): bool
    {
        return $this->hasSentAppsQuery()->exists();
    }

    public function getHasTargetReceptions(): bool
    {
        return $this->getApplications()->innerJoinWith('bachelorTargetReceptions', false)->exists();
    }

    public function getHasPreferences(): bool
    {
        return $this->getApplications()->innerJoinWith('preferences', false)->exists();
    }

    public function getHasEntrantTests(): bool
    {
        return $this->getApplications()->innerJoinWith('egeResults', false)->exists();
    }

    public function getHasFullCostRecovery(): bool
    {
        return $this->getApplications()
            ->joinWith(['allBachelorSpecialities' => function ($q) {
                $q->joinWith(['speciality.educationSourceRef education_source_ref'], false);
            }], false)
            ->andWhere([
                'education_source_ref.reference_uid' => \Yii::$app->configurationManager->getCode('full_cost_recovery_guid')
            ])
            ->exists();
    }

    


    public function getHumanApplicationStatuses(): string
    {
        $applications = $this->applications;
        return implode(
            ', ',
            array_map(
                function (BachelorApplication $application) {
                    return BachelorApplication::rawTranslateStatus($application->status);
                },
                $applications
            )
        );
    }

    public function getResubmitPermissions()
    {
        return $this->hasMany(ApplicationResubmitPermission::class, ['user_id' => 'id']);
    }

    public function getCampaignsToModerate()
    {
        return $this->hasMany(ApplicationType::class, ['id' => 'application_type_id'])
            ->viaTable(ManageAC::tableName(), ['rbac_auth_assignment_user_id' => 'id']);
    }

    public function hasCampaignsToModerateWithRestrictedResubmission(): bool
    {
        return $this->getCampaignsToModerate()
            ->joinWith(['applicationTypeSettings application_type_settings'])
            ->andWhere(['application_type_settings.name' => 'allow_secondary_apply_after_approval', 'application_type_settings.value' => [null, 0]])
            ->exists();
    }
}
