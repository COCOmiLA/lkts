<?php

namespace common\modules\student\components;

use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use stdClass;
use Yii;

class AuthManager extends \yii\base\Component
{
    public $login;
    public $password;

    public $serviceUrl;

    protected $client;

    public function getUserInfoByCredentials($login, $password): array
    {
        $response = Yii::$app->soapClientStudent->load('Authorization',
            [
                'UserId' => '',
                'Login' => $login,
                'PasswordHash' => sha1($password)
            ]
        );
        $error_msg = null;
        $data = null;
        if (isset($response->return->Error) && isset($response->return->Error->Description)) {
            $error_msg = $response->return->Error->Description;
        }
        if (isset($response->return->User) && $response->return->User != null) {
            $data = $response->return->User;
            $data->Roles = $this->processRoles($data->Roles ?? [], $data->UserId);
            if (!isset($data->UserRef)) {
                $referenceType = ReferenceTypeManager::GetReferenceTypeFrom1C($data->UserId, StoredUserReferenceType::getReferenceClassToFill(), 'Код');
                if ($referenceType) {
                    $data->UserRef = $referenceType->toObject();
                }
            }
        }

        return [$data, $error_msg];
    }

    




    protected function processRoles($roles, string $user_id): array
    {
        $roles_array = [];

        if ($roles) {
            if (!is_array($roles)) {
                $roles = [$roles];
            }
            foreach ($roles as $role) {
                $roles_array[] = $role->Role;
            }
        }

        if (!in_array("Abiturient", $roles_array)) {
            $roles_array[] = "Abiturient";
        }

        $user_id = trim($user_id);
        Yii::$app->session->set('_' . $user_id, $roles_array);
        return $roles_array;
    }

    public function getRoles($user_id)
    {
        if (Yii::$app->session->has('_' . $user_id)) {
            return Yii::$app->session->get('_' . $user_id);
        }

        return null;
    }

    protected function buildUrl()
    {

        if (substr($this->serviceUrl, -1) != '/') {
            $urlTemplate = $this->serviceUrl . '/';
        } else {
            $urlTemplate = $this->serviceUrl;
        }

        $url = $urlTemplate;


        return $url;
    }

    protected function BuildUserArrayFromXML($data, $login, $hash)
    {
        $xml_response = simplexml_load_string($data);

        if ($xml_response->getName() == 'error') {
            $log = [
                'data' => [
                    'url' => $this->buildUrl(),
                    'login' => $login,
                    'hash' => $hash,
                ],
                'response_content' => $data,
            ];
            Yii::error('Ошибка получения данных о пользователе: ' . PHP_EOL . print_r($log, true));
            return null;
        }
        $user_array = [];
        $user_array['guid'] = (string)$xml_response->id;
        $user_array['username'] = (string)$xml_response->name;
        $user_array['password'] = (string)$xml_response->password;
        $roles = [];
        foreach ($xml_response->roles->role as $role) {
            $roles[] = (string)$role;
        }

        $user_array['roles'] = $roles;
        $regnums = [];
        foreach ($xml_response->recordbooks->recordbook as $recordbook) {
            $regnums[] = (string)$recordbook;
        }
        $user_array['reg_numbers'] = $regnums;

        return $user_array;
    }

    protected function BuildRoles($data, $user_id)
    {
        $xml_response = simplexml_load_string($data);

        if ($xml_response->getName() == 'error') {
            $log = [
                'data' => [
                    'url' => $this->buildUrl(),
                    'user_id' => $user_id,
                ],
                'response_content' => $data,
            ];
            Yii::error('Ошибка получения ролей пользователя: ' . PHP_EOL . print_r($log, true));
            return null;
        }

        $roles = [];
        foreach ($xml_response->roles->role as $role) {
            $roles[] = (string)$role;
        }

        return $roles;
    }
}
