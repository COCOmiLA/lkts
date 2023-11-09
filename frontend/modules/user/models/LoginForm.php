<?php

namespace frontend\modules\user\models;

use cheatsheet\Time;
use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\Recaptcha;
use common\models\Rolerule;
use common\models\User;
use common\modules\student\components\AuthManager;
use stdClass;
use Yii;
use yii\base\Model;
use yii\base\UserException;
use yii\db\Expression;
use yii\db\Query;





class LoginForm extends Model
{
    public $identity;
    public $password;
    public $rememberMe = true;

    
    private $user = false;

    private $_credentials = null;
    private $_credentials_received = false;
    private $_credentials_error = null;

    public $reCaptcha;

    public $needToChooseRole = false;

    public function __construct($config = [])
    {
        parent::__construct($config);
        if (Yii::$app->configurationManager->getAllowRememberMe()) {
            $this->rememberMe = false;
        }
    }
    
    


    public function rules()
    {
        $rules = [
            
            ['identity', 'filter', 'filter' => 'trim'],
            [['identity', 'password'], 'required'],
            
            ['rememberMe', 'boolean'],
        ];

        $validator = Recaptcha::getValidationArrayByName('login');
        if (!empty($validator)) {
            $rules[] = $validator;
        }

        return $rules;
    }

    public function attributeLabels()
    {
        $identityLabel = Yii::t(
            'sign-in/login/form',
            'Заголовок поля "identity" на форме авторизации: `Электронная почта или имя пользователя`'
        );

        return [
            'identity' => $identityLabel,
            'password' => Yii::t(
                'sign-in/login/form',
                'Заголовок поля "password" на форме авторизации: `Пароль`'
            ),
            'rememberMe' => Yii::t(
                'sign-in/login/form',
                'Заголовок поля "rememberMe" на форме авторизации: `Запомнить меня`'
            ),
        ];
    }

    



    public function validateUserAndPasswordBy1C()
    {
        if (!$this->credentials) {
            $error_text = $this->getCredentialsError();
            if (!trim((string)$error_text)) {
                $error_text = Yii::t(
                    'sign-in/login/form',
                    'Подсказка с ошибкой для поля "password" на форме авторизации: `Неправильный логин или пароль.`'
                );
            }
            $this->addError(
                'password',
                $error_text
            );
            return false;
        }
        return true;
    }

    protected function needToBeValidIn1C(?User $user): bool
    {
        $update_from_credentials = true;
        if ($user) {
            $roles = Yii::$app->authManager->getRolesByUser($user->id);
            if (
                count($roles) == 1 &&
                (
                    isset($roles[User::ROLE_VIEWER]) ||
                    isset($roles[User::ROLE_MANAGER]) ||
                    isset($roles[User::ROLE_ABITURIENT]) ||
                    isset($roles[User::ROLE_ADMINISTRATOR])
                )
            ) {
                $update_from_credentials = false;
            }
        }
        return $update_from_credentials;
    }

    




    public function login()
    {
        if (!$this->validate()) {
            return false;
        }
        
        $this->user = $this->findUserByUsernameAndPassword($this->identity, $this->password);
        $needToBeValidIn1C = true;
        if ($this->user) {
            $needToBeValidIn1C = $this->needToBeValidIn1C($this->user);
        }

        
        $valid_by_1C = $this->validateUserAndPasswordBy1C();
        if ($valid_by_1C) {
            if (!$this->user) {
                $users = $this->findUsersByUsername($this->identity);
                if (count($users) > 1) {
                    throw new UserException("Не удалось распознать пользователя");
                }
                $this->user = $users[0] ?? null;
                if ($this->user) {
                    $this->user->setPassword($this->password);
                    $this->user->save();
                }
            }
            $this->user = $this->updateOrCreateUserFromCredentials($this->user);
        }
        if ($needToBeValidIn1C && !$valid_by_1C) {
            $this->user = null;
        }
        
        $rememberMeDuration = Yii::$app->configurationManager->getIdentityCookieDuration();
        
        if ($this->user && Yii::$app->user->login($this->user, $this->rememberMe ? $rememberMeDuration : 0)) {
            if ($this->credentials != null) {
                $this->SetRecordbooks($this->credentials->UserId);
            }
            return true;
        }
        return false;
    }

    



    private function findUsersByUsername(string $username): array
    {
        
        $new_table = (new Query())
            ->select([
                IndependentQueryManager::toBinary('LOWER(username)') . ' binary_lower_username',
                IndependentQueryManager::toBinary('LOWER(email)') . ' binary_lower_email',
                'id'
            ])
            ->from('user');
        
        $result = (new Query())
            ->from($new_table)
            ->where(new Expression(
                'binary_lower_username = ' . IndependentQueryManager::toBinary('LOWER(:username)'),
                [':username' => $username]
            ))
            ->orWhere(new Expression(
                'binary_lower_email = ' . IndependentQueryManager::toBinary('LOWER(:email)'),
                [':email' => $username]
            ))
            ->all();

        
        return User::findActive()
            ->andWhere(['id' => $result])
            ->andWhere(['user.is_archive' => false])
            ->all();
    }

    private function findUserByUsernameAndPassword(string $username, string $password): ?User
    {
        $users = $this->findUsersByUsername($username);
        foreach ($users as $user) {
            if (Yii::$app->security->validatePassword($password, $user->password_hash)) {
                return $user;
            }
        }
        return null;
    }

    public function getCredentials(): ?stdClass
    {
        if (!$this->_credentials_received) {
            $this->setUpCredentials();
        }
        return $this->_credentials;
    }

    public function getCredentialsError(): ?string
    {
        return $this->_credentials_error;
    }

    protected function setUpCredentials(): void
    {
        $rolerule = Rolerule::find()->limit(1)->one();
        if (!$rolerule) {
            $this->_credentials_error = 'Не найдена таблица ролей';
            $this->_credentials_received = true;
            return;
        }
        if ($rolerule->student || $rolerule->teacher) {
            if (Yii::$app->hasModule('student')) {
                
                $authManager = Yii::$app->getModule('student')->authManager;
                [$this->_credentials, $this->_credentials_error] = $authManager->getUserInfoByCredentials($this->identity, $this->password);
            }
        }
        $this->_credentials_received = true;
    }

    protected function updateOrCreateUserFromCredentials(?User $user): ?User
    {
        $auth = Yii::$app->authManager;
        
        if ($this->credentials == null) {
            return $user;
        }
        $userRef = UserReferenceTypeManager::getUserReferenceFrom1CByGuid($this->credentials->UserRef->ReferenceUID, 'Идентификатор');

        if ($user == null) {
            $user = User::find()
                ->where([
                    'or',
                    ['guid' => $this->credentials->UserRef->ReferenceId],
                    ['user_ref_id' => $userRef->id]
                ])
                ->one();
            if ($user == null) {
                $user = new User();
            }
        }
        if (!$user->getIsNewRecord()) {
            $auth->revokeAll($user->id);
        }

        $user->username = $this->credentials->Login;
        $user->guid = $this->credentials->UserRef->ReferenceId;
        $user->user_ref_id = $userRef->id;
        $user->setPassword($this->password);

        $user->save();

        if ($user->userProfile == null) {
            $profile = new \common\models\UserProfile();
            $profile->locale = Yii::$app->language;
            $user->link('userProfile', $profile);
            $profile->save();
        }

        try {
            $auth->assign($auth->getRole(User::ROLE_USER), $user->getId());
        } catch (\Throwable $e) {
        }

        $rolesList = [];
        if (isset($this->credentials->Roles)) {
            if (!is_array($this->credentials->Roles)) {
                $this->credentials->Roles = [$this->credentials->Roles];
            }
            $rolesList = $this->credentials->Roles; 
        }
        if (count($rolesList) == 1) {
            $role = ucfirst($rolesList[0]);
            if ($role == 'Student') {
                $auth->assign($auth->getRole(User::ROLE_STUDENT), $user->getId());
            } elseif ($role == 'Teacher') {
                $auth->assign($auth->getRole(User::ROLE_TEACHER), $user->getId());
            } elseif ($role == 'Abiturient') {
                $auth->assign($auth->getRole(User::ROLE_ABITURIENT), $user->getId());
            }
        } elseif ($rolesList) {
            $this->needToChooseRole = true;
        }

        return User::findOne($user->id);
    }

    public function SetRecordbooks(string $user_id)
    {
        $recordbooks = Yii::$app->getPortfolioService->loadRawRecordbooks($user_id, true);
        if (!$recordbooks) {
            Yii::$app->session->setFlash('ErrorRecordbooks', true, false);
        }
    }
}
