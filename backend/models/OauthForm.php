<?php

namespace backend\models;

use filsh\yii2\oauth2server\models\OauthClients;
use yii\base\Model;





class OauthForm extends Model
{
    public $client_id;
    public $client_secret;
    public $redirect_uri;
    public $grant_types;
    public $user_id;
    public $scope;

    private $model;

    


    public function rules()
    {
        return [
            [['client_id', 'client_secret', 'redirect_uri', 'grant_types'], 'required'],
            [['user_id'], 'integer'],
            [['client_id', 'client_secret'], 'string', 'max' => 32],
            [['redirect_uri'], 'string', 'max' => 1000],
            [['grant_types'], 'string', 'max' => 100],
            [['scope'], 'string', 'max' => 2000]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'client_id' => 'ID клиента',
            'client_secret' => 'Секрет клиента',
            'redirect_uri' => 'Uri для перенаправления',
            'grant_types' => 'Grant Types',
            'scope' => 'Scope',
            'user_id' => 'User ID',
        ];
    }

    public function setModel($model)
    {
        $this->client_id = $model->client_id;
        $this->client_secret = $model->client_secret;
        $this->redirect_uri = $model->redirect_uri;
        $this->grant_types = $model->grant_types;
        $this->user_id = $model->user_id;
        $this->scope = $model->scope;

        return $this->model;
    }

    public function getModel()
    {
        if (!$this->model) {
            $this->model = new OauthClients();
        }
        return $this->model;
    }

    




    public function save($isNew)
    {
        if ($this->validate()) {

            $model = $this->getModel();
            $model->client_id = $this->client_id;
            $model->client_secret = $this->client_secret;
            $model->redirect_uri = $this->redirect_uri;
            $model->grant_types = $this->grant_types;
            $model->user_id = $this->user_id;
            $model->scope = $this->scope;

            if (!$model->grant_types) {
                $model->grant_types = "client_credentials authorization_code password implicit";
            }

            if (!$isNew) {
                $model->update();
            } else {
                $model->save();
            }

            return !$model->hasErrors();
        }
        return null;
    }
}

