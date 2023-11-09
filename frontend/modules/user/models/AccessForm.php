<?php

namespace frontend\modules\user\models;

use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\EntityForDuplicatesFind;
use common\models\Recaptcha;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\web\NotAcceptableHttpException;




class AccessForm extends Model
{
    const ERROR_NO_CODE = 1;
    const NO_ERROR = -1;

    public $identity;
    public $passportSeries;
    public $passportNumber;
    


    public $error_code = null;
    public $secretCode;
    public $password;
    public $firstname;
    public $secondname;
    public $lastname;
    public $passwordRepeat;
    public $birth_date;
    public $documentTypeCode;
    public $documentTypeId;
    


    public $possibleEmail;
    


    public $user_ref_id;

    private $user = false;

    public $reCaptcha;

    


    public function rules()
    {
        $rules = [
            [['identity', 'passportSeries', 'passportNumber', 'firstname', 'secondname', 'lastname', 'password', 'passwordRepeat', 'birth_date', 'documentTypeCode'], 'string'],
            [['firstname', 'lastname', 'birth_date'], 'required'],
            [['passportNumber', 'passportSeries'], 'required', 'when' => function ($model) {
                return $model->documentTypeId === CodeSettingsManager::GetEntityByCode('russian_passport_guid')->id;
            }, 'whenClient' => "function (attribute, value) {
                    var id = $('#doc_type').val();
                    if(id == '" . CodeSettingsManager::GetEntityByCode('russian_passport_guid')->id . "')
                    {
                            return true;
                    }
            }"],
            ['password', 'compare', 'compareAttribute' => 'passwordRepeat', 'operator' => '==', 'message' => 'Введенные пароли не совпадают'],
            ['password', 'string', 'min' => 6, 'when' => function ($model) {
                return (!Yii::$app->configurationManager->signupEmailEnabled);
            }],
            [['documentTypeId'], 'exist', 'skipOnError' => false, 'targetClass' => DocumentType::class, 'targetAttribute' => ['documentTypeId' => 'id']],
        ];

        $validator = Recaptcha::getValidationArrayByName('abit_access');
        if (!empty($validator)) {
            $rules[] = $validator;
        }

        return $rules;
    }

    public function __construct($config = [])
    {
        $this->error_code = 0;
        parent::__construct($config);
    }

    public function attributeLabels()
    {
        return [
            'identity' => 'Имя пользователя',
            'passportSeries' => 'Серия паспорта',
            'passportNumber' => 'Номер паспорта',
            'secretCode' => 'Секретный код',
            'password' => 'Пароль',
            'passwordRepeat' => 'Повторите пароль',
            'firstname' => 'Имя',
            'lastname' => 'Фамилия',
            'secondname' => 'Отчество',
            'birth_date' => 'Дата рождения',
            'documentTypeCode' => 'Тип документа',
            'documentTypeId' => 'Тип документа',
        ];
    }


    public function getAccess()
    {
        if ($this->validate()) {
            $found_doubles = Yii::$app->authentication1CManager->getAbiturientDoublesByFullInfo($this->getEntityForDuplicatesFind());
            if ($found_doubles) {
                if (count($found_doubles) > 1) {
                    throw new NotAcceptableHttpException('Найдено несколько пользователей с одним кодом. Обратитесь к администратору приемной кампании.');
                }
                $matched = $found_doubles[0];
                $user_ref = ReferenceTypeManager::GetOrCreateReference(StoredUserReferenceType::class, $matched->EntrantRef);

                $this->user_ref_id = $user_ref->id;
                
                Yii::$app->session->set('abitRefIdToRecover', $user_ref->id);
                
                $users = User::findActive('u')
                    ->alias('u')
                    ->leftJoin('rbac_auth_assignment', 'rbac_auth_assignment.user_id = u.id')
                    ->andWhere(['or', ['user_ref_id' => $user_ref->id], ['guid' => $user_ref->reference_id]])
                    ->andWhere(['rbac_auth_assignment.item_name' => 'abiturient'])
                    ->andWhere(['status' => 1]);
                if ($users->count() > 1) {
                    throw new NotAcceptableHttpException('Найдено несколько пользователей с одним кодом. Обратитесь к администратору приемной кампании.');
                }
                $user = $users->one();
                if ($user != null) {
                    $this->possibleEmail = $user->email;
                    $this->error_code = self::NO_ERROR;
                    return true;
                }
            }
        }
        return false;
    }

    




    public function getUser()
    {
        if ($this->user === false) {
            $this->user = User::findActive()->andWhere(['or', ['username' => $this->identity], ['email' => $this->identity]])->one();
        }

        return $this->user;
    }

    public function getUserByCode($code)
    {
        if ($this->user === null) {
            $this->user = User::findOne(['guid' => $code]);
        }

        return $this->user;
    }

    public function getHiddenEmail()
    {
        $matches = [];
        $email = $this->possibleEmail;
        if (preg_match("/(.*)@(.*)\.(.*)/", $email, $matches)) {
            if (!empty($matches[1])) {
                $len = strlen((string)$matches[1]) / 2;
                $email = substr_replace($email, str_repeat('*', $len), 0, $len);
            }
        } else {
            $email = substr_replace($email, '*', 0, 3);
        }
        return $email;
    }

    public function getEntityForDuplicatesFind(): EntityForDuplicatesFind
    {
        return new EntityForDuplicatesFind(
            $this->firstname,
            $this->lastname,
            $this->secondname,
            $this->birth_date,
            null,
            [
                [
                    'type' => DocumentType::findOne($this->documentTypeId),
                    'series' => $this->passportSeries,
                    'number' => $this->passportNumber,
                ]
            ]
        );
    }
}
