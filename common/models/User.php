<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;

class User extends ActiveRecord implements IdentityInterface
{
    // Add missing safe attributes (virtual attributes)
    public $avatarFile;   // file upload ke liye virtual attribute
    public $password;
    public $confirm_password;

    const ROLE_USER  = 0;
    const ROLE_ADMIN = 1;



    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    public static function tableName()
    {
        return '{{%user}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [

            [['first_name', 'last_name', 'email', 'username'], 'trim'],
            [['first_name', 'last_name', 'email', 'username'], 'required', 'on' => ['create', 'update', 'api-register']],

            [['first_name', 'last_name'], 'string', 'min' => 2, 'max' => 50],
            [['first_name', 'last_name'], 'match', 'pattern' => '/^[A-Za-z ]+$/', 'message' => 'Only alphabets allowed.'],

            ['username', 'string', 'min' => 3, 'max' => 30],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9_]+$/', 'message' => 'Only letters, numbers and underscores allowed.'],
            ['username', 'unique', 'targetClass' => self::class, 'filter' => ['!=', 'id', $this->id], 'message' => 'This username is already taken.'],

            ['email', 'email'],
            ['email', 'string', 'max' => 100],
            ['email', 'unique', 'targetClass' => self::class, 'filter' => ['!=', 'id', $this->id], 'message' => 'This email is already taken.'],

            // Avatar upload
            ['avatarFile', 'file', 'extensions' => ['jpg', 'jpeg', 'png', 'webp'], 'maxSize' => 2 * 1024 * 1024, 'skipOnEmpty' => true],
            ['avatarFile', 'required', 'on' => 'upload'],

            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['role', 'safe'],

            ['password', 'required', 'on' => ['create', 'api-register']],
            ['password', 'string', 'min' => 6],
            ['confirm_password', 'required', 'on' => 'create'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],

            [['pending_email'], 'safe'],
            [['pending_email'], 'email'],
            [['access_token'], 'safe'],
        ];
    }

    public function scenarios()
{
    $scenarios = parent::scenarios();

    $scenarios[self::SCENARIO_DEFAULT][] = 'access_token';

    $scenarios['upload'] = [
        'avatarFile', 'first_name', 'last_name', 'email', 'username', 'access_token'
    ];

    $scenarios['create'] = [
        'first_name','last_name','email','username',
        'role','status','avatarFile','password','confirm_password','access_token'
    ];

    $scenarios['update'] = [
        'first_name','last_name','email','username',
        'role','status','avatarFile','password','confirm_password','access_token'
    ];

    $scenarios['api-register'] = [
        'first_name','last_name','email','username','password','access_token'
    ];

    return $scenarios;
}







    // OPTIONAL: get full avatar URL for view
    public function getAvatarUrl()
    {
        if ($this->avatar && file_exists(Yii::getAlias('@webroot/uploads/avatars/' . $this->avatar))) {
            return '/uploads/avatars/' . $this->avatar;
        }

        return '/images/default-avatar.png';
    }
    
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
{
    return static::findOne([
        'access_token' => $token,
        'status' => self::STATUS_ACTIVE
    ]);
}

    public function getRoleName(): string
    {
        return $this->role === self::ROLE_ADMIN ? 'admin' : 'user';
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

public static function findByVerificationToken($token)
{
    return static::find()
        ->andWhere(['verification_token' => $token])
        ->andWhere(['status' => self::STATUS_INACTIVE])
        ->one();
}



public static function isVerificationTokenValid($token)
{
    if (empty($token)) {
        return false;
    }

    $timestamp = (int) substr($token, strrpos($token, '_') + 1);
    $expire = Yii::$app->params['user.verificationTokenExpire']; // same as signup
    return $timestamp + $expire >= time();
}


    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
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

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generateAccessToken()
{
    $this->access_token = Yii::$app->security->generateRandomString(64);
}


    public function removeEmailVerificationToken()
    {
        $this->verification_token = null;
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function isAdmin()
    {
        return $this->role === 1;
    }

    public function isUser()
    {
        return $this->role === 0;
    }

    public static function getUsersList()
{
    return \yii\helpers\ArrayHelper::map(
        self::find()->all(),
        'id',
        'username'
    );
}

public static function findByUsernameApi($username)
{
    return static::findOne([
        'username' => $username,
        'status' => self::STATUS_ACTIVE
    ]);
}



public function getTeamMembers()
{
    return $this->hasMany(\common\models\TeamMembers::class, ['user_id' => 'id'])
        ->with('team');
}

public function getTeams()
{
    return $this->hasMany(\common\models\Team::class, ['id' => 'team_id'])
        ->via('teamMembers');
}

}
