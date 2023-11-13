<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\db\UserQuery;
use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Activity;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Meta;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Mod;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Exception;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\IdentityInterface;



























abstract class UserActiveRecord extends ActiveRecord implements IdentityInterface
{
    


    const STATUS_REGISTERED = 1;
    const STATUS_BANNED     = 9;
    const STATUS_ACTIVE     = 10;

    


    public static function tableName()
    {
        return '{{%podium_user}}';
    }

    


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class'     => SluggableBehavior::class,
                'attribute' => 'username',
            ],
        ];
    }

    


    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    



    public function passwordRequirements($attribute)
    {
        if (!preg_match('~\p{Lu}~', $this->$attribute) ||
            !preg_match('~\p{Ll}~', $this->$attribute) ||
            !preg_match('~[0-9]~', $this->$attribute) ||
            mb_strlen((string)$this->$attribute, 'UTF-8') < 6 ||
            mb_strlen((string)$this->$attribute, 'UTF-8') > 100) {
            $this->addError($attribute, Yii::t('podium/view', 'Password must contain uppercase and lowercase letter, digit, and be at least 6 characters long.'));
        }
    }

    




    public static function findByActivationToken($token)
    {
        if (!static::isActivationTokenValid($token)) {
            return null;
        }
        return static::find()->where(['activation_token' => $token, 'status' => self::STATUS_REGISTERED])->limit(1)->one();
    }

    




    public static function findByEmail($email)
    {
        return static::find()->where(['email' => $email, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }

    




    public static function findByEmailToken($token)
    {
        if (!static::isEmailTokenValid($token)) {
            return null;
        }
        return static::find()->where(['email_token' => $token, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }

    





    public static function findByKeyfield($keyfield, $status = self::STATUS_ACTIVE)
    {
        if ($status === null) {
            return static::find()->where(['or', ['email' => $keyfield], ['username' => $keyfield]])->limit(1)->one();
        }
        return static::find()->where(['and', ['status' => $status], ['or', ['email' => $keyfield], ['username' => $keyfield]]])->limit(1)->one();
    }

    





    public static function findByPasswordResetToken($token, $status = self::STATUS_ACTIVE)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        if ($status == null) {
            return static::find()->where(['password_reset_token' => $token])->limit(1)->one();
        }
        return static::find()->where(['password_reset_token' => $token, 'status' => $status])->limit(1)->one();
    }

    




    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }

    


    public static function findIdentity($id)
    {
        try {
            return static::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
        } catch (Exception $exc) {
            Log::warning('Podium is not installed correctly!', null, __METHOD__);
            return null;
        }
    }

    


    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    


    public function generateActivationToken()
    {
        $this->activation_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    


    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    


    public function generateEmailToken()
    {
        $this->email_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    


    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    


    public function getAuthKey()
    {
        return $this->auth_key;
    }

    


    public function getId()
    {
        return $this->getPrimaryKey();
    }

    



    public function getActivity()
    {
        return $this->hasOne(Activity::class, ['user_id' => 'id']);
    }

    




    public function getFriends()
    {
        return $this->hasMany(static::class, ['id' => 'friend_id'])->viaTable('{{%podium_user_friend}}', ['user_id' => 'id']);
    }

    



    public function getMeta()
    {
        return $this->hasOne(Meta::class, ['user_id' => 'id']);
    }

    




    public function getMods()
    {
        return $this->hasMany(Mod::class, ['user_id' => 'id']);
    }

    




    public static function isActivationTokenValid($token)
    {
        $expire = Podium::getInstance()->podiumConfig->get('activation_token_expire');
        if ($expire === null) {
            $expire = 3 * 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }

    




    public static function isEmailTokenValid($token)
    {
        $expire = Podium::getInstance()->podiumConfig->get('email_token_expire');
        if ($expire === null) {
            $expire = 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }

    




    public static function isPasswordResetTokenValid($token)
    {
        $expire = Podium::getInstance()->podiumConfig->get('password_reset_token_expire');
        if ($expire === null) {
            $expire = 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }

    





    public static function isTokenValid($token, $expire)
    {
        if (empty($token) || empty($expire)) {
            return false;
        }
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        return $timestamp + (int)$expire >= time();
    }

    


    public function removeActivationToken()
    {
        $this->activation_token = null;
    }

    


    public function removeEmailToken()
    {
        $this->email_token = null;
    }

    


    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    



    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    


    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    



    public function validateCurrentPassword($attribute)
    {
        if (!$this->hasErrors()) {
            if (!$this->validatePassword($this->currentPassword)) {
                $this->addError($attribute, Yii::t('podium/view', 'Current password is incorrect.'));
            }
        }
    }

    







    public function validatePassword($password)
    {
        $podium = Podium::getInstance();
        if ($podium->userComponent !== true) {
            $password_hash = empty($podium->userPasswordField) ? 'password_hash' : $podium->userPasswordField;
            if (!empty($podium->user->identity->$password_hash)) {
                return Yii::$app->security->validatePassword($password, $podium->user->identity->$password_hash);
            }
            return false;
        }
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    




    public function validateUsername($attribute)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[\p{L}][\w\p{L}]{2,254}$/u', $this->username)) {
                $this->addError($attribute, Yii::t('podium/view', 'Username must start with a letter, contain only letters, digits and underscores, and be at least 3 characters long.'));
            }
        }
    }
}
