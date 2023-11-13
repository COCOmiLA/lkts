<?php

namespace backend\models;

use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;




class UserForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $status;
    public $roles;

    private $model;

    


    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'filter' => function ($query) {
                if (!$this->getModel()->isNewRecord) {
                    $query->andWhere(['not', ['id' => $this->getModel()->id]]);
                }
            }],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'filter' => function ($query) {
                if (!$this->getModel()->isNewRecord) {
                    $query->andWhere(['not', ['id' => $this->getModel()->id]]);
                }
            }],

            ['password', 'required', 'on' => 'create'],
            ['password', 'string', 'min' => 6],

            [['status'], 'boolean'],
            [['roles'], 'each',
                'rule' => ['in', 'range' => ArrayHelper::getColumn(
                    Yii::$app->authManager->getRoles(),
                    'name'
                )]
            ],
            ['roles', 'required'],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'username' => Yii::t('backend', 'Имя пользователя'),
            'email' => Yii::t('backend', 'Email'),
            'password' => Yii::t('backend', 'Пароль'),
            'roles' => Yii::t('backend', 'Роли')
        ];
    }

    public function setModel($model)
    {
        $this->username = $model->username;
        $this->email = $model->email;
        $this->status = $model->status;
        $this->model = $model;
        $this->roles = ArrayHelper::getColumn(
            Yii::$app->authManager->getRolesByUser($model->getId()),
            'name'
        );
        return $this->model;
    }

    public function getModel()
    {
        if (!$this->model) {
            $this->model = new User();
        }
        return $this->model;
    }

    




    public function save()
    {
        if ($this->validate()) {
            $model = $this->getModel();
            $isNewRecord = $model->getIsNewRecord();
            $model->username = $this->username;
            $model->email = $this->email;
            $model->status = $this->status;
            $password_changed = false;
            $pass_len = 0;
            if ($this->password) {
                $model->setPassword($this->password);
                $password_changed = true;
                $pass_len = mb_strlen((string)$this->password);
            }
            if ($model->save() && $isNewRecord) {
                $model->addUserRegistrationConfirm();
                $model->afterSignup();
            }
            $auth = Yii::$app->authManager;
            $auth->revokeAll($model->getId());

            if ($this->roles && is_array($this->roles)) {
                if (in_array(User::ROLE_ADMINISTRATOR, $this->roles) && in_array(User::ROLE_MANAGER, $this->roles)) {
                    Yii::$app->session->setFlash('alert', [
                        'body' => 'Нельзя одновременно быть администратором и менеджером',
                        'options' => ['class' => 'alert-danger']
                    ]);
                    return false;
                }
                foreach ($this->roles as $role) {
                    $auth->assign($auth->getRole($role), $model->getId());
                }
            }
            Yii::warning("Изменение данных пользователя {$model->id} {$model->username}" . ($password_changed ? ", изменён пароль: {$model->password_hash}, пароль содержал {$pass_len} символов" : ''));
            return !$model->hasErrors();
        }
        return false;
    }
}
