<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use yii\base\NotSupportedException;

/**
 * User model
 *
 * This model represents the `user` table.
 * It handles authentication, authorization, profile data,
 * password management, tokens, and team relations.
 */
class User extends ActiveRecord implements IdentityInterface
{
    /* ================= VIRTUAL ATTRIBUTES ================= */

    public $avatarFile;        // Avatar upload ke liye virtual attribute
    public $password;          // Plain password (hash nahi hota)
    public $confirm_password;  // Password confirmation (form validation)

    /* ================= ROLE CONSTANTS ================= */

    const ROLE_USER  = 0;
    const ROLE_ADMIN = 1;

    /* ================= STATUS CONSTANTS ================= */

    const STATUS_DELETED  = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE   = 10;

    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * Attaches timestamp behavior.
     * Automatically sets created_at and updated_at.
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Validation rules for User model.
     */
    public function rules()
    {
        return [
            // Trim inputs
            [['first_name', 'last_name', 'email', 'username'], 'trim'],

            // Required fields (create, update, api-register)
            [['first_name', 'last_name', 'email', 'username'], 'required', 'on' => ['create', 'update', 'api-register']],

            // Name validation
            [['first_name', 'last_name'], 'string', 'min' => 2, 'max' => 50],
            [['first_name', 'last_name'], 'match', 'pattern' => '/^[A-Za-z ]+$/', 'message' => 'Only alphabets allowed.'],

            // Username validation
            ['username', 'string', 'min' => 3, 'max' => 30],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9_]+$/', 'message' => 'Only letters, numbers and underscores allowed.'],
            ['username', 'unique', 'targetClass' => self::class, 'filter' => ['!=', 'id', $this->id], 'message' => 'This username is already taken.'],

            // Email validation
            ['email', 'email'],
            ['email', 'string', 'max' => 100],
            ['email', 'unique', 'targetClass' => self::class, 'filter' => ['!=', 'id', $this->id], 'message' => 'This email is already taken.'],

            // Avatar upload validation
            ['avatarFile', 'file', 'extensions' => ['jpg', 'jpeg', 'png', 'webp'], 'maxSize' => 2 * 1024 * 1024, 'skipOnEmpty' => true],
            ['avatarFile', 'required', 'on' => 'upload'],

            // Status validation
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            ['status', 'default', 'value' => self::STATUS_INACTIVE],

            // Role safe
            ['role', 'safe'],

            // Password rules
            ['password', 'required', 'on' => ['create', 'api-register']],
            ['password', 'string', 'min' => 6],
            ['confirm_password', 'required', 'on' => 'create'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],

            // Tokens and pending email
            [['pending_email', 'access_token'], 'safe'],
            [['pending_email'], 'email'],
        ];
    }

    /**
     * Defines scenarios for different use cases.
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        // Default scenario
        $scenarios[self::SCENARIO_DEFAULT][] = 'access_token';

        // Avatar upload scenario
        $scenarios['upload'] = [
            'avatarFile', 'first_name', 'last_name', 'email', 'username', 'access_token'
        ];

        // Create user scenario
        $scenarios['create'] = [
            'first_name', 'last_name', 'email', 'username',
            'role', 'status', 'avatarFile', 'password', 'confirm_password', 'access_token'
        ];

        // Update user scenario
        $scenarios['update'] = [
            'first_name', 'last_name', 'email', 'username',
            'role', 'status', 'avatarFile', 'password', 'confirm_password', 'access_token'
        ];

        // API register scenario
        $scenarios['api-register'] = [
            'first_name', 'last_name', 'email', 'username', 'password', 'access_token'
        ];

        return $scenarios;
    }

    /* ================= HELPER METHODS ================= */

    /**
     * Returns full avatar URL for views.
     */
    public function getAvatarUrl()
    {
        if ($this->avatar && file_exists(Yii::getAlias('@webroot/uploads/avatars/' . $this->avatar))) {
            return '/uploads/avatars/' . $this->avatar;
        }

        return '/images/default-avatar.png';
    }

    /* ================= IDENTITY INTERFACE ================= */

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
        return $this->auth_key === $authKey;
    }

    /* ================= AUTH HELPERS ================= */

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

    /* ================= TOKEN HELPERS ================= */

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

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function removeEmailVerificationToken()
    {
        $this->verification_token = null;
    }

    /* ================= FINDERS ================= */

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByUsernameApi($username)
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

    /* ================= TOKEN VALIDATION ================= */

    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }

    public static function isVerificationTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.verificationTokenExpire'];

        return $timestamp + $expire >= time();
    }

    /* ================= ROLE HELPERS ================= */

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser()
    {
        return $this->role === self::ROLE_USER;
    }

    public function getRoleName(): string
    {
        return $this->role === self::ROLE_ADMIN ? 'admin' : 'user';
    }

    /* ================= LIST HELPERS ================= */

    public static function getUsersList()
    {
        return \yii\helpers\ArrayHelper::map(
            self::find()->all(),
            'id',
            'username'
        );
    }

    /* ================= TEAM RELATIONS ================= */

    public function getTeamMembers()
    {
        return $this->hasMany(TeamMembers::class, ['user_id' => 'id'])
            ->with('team');
    }

    public function getTeams()
    {
        return $this->hasMany(Team::class, ['id' => 'team_id'])
            ->via('teamMembers');
    }
}
