<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use common\models\User;
use common\components\StripeService;

/**
 * SignupForm model
 *
 * This model handles user registration.
 * It validates user input, creates a new user,
 * uploads avatar, creates Stripe customer,
 * and sends email verification link.
 */
class SignupForm extends Model
{
    public $first_name;
    public $last_name;
    public $username;
    public $email;
    public $password;
    public $confirm_password;

    /**
     * Virtual attribute for avatar upload
     * (not stored directly in database)
     */
    public $avatarFile;

    /**
     * Validation rules for signup form.
     */
    public function rules()
    {
        return [

            /* ---------- BASIC SANITIZATION ---------- */
            [['first_name', 'last_name', 'username', 'email'], 'trim'],

            /* ---------- REQUIRED FIELDS ---------- */
            [['first_name', 'last_name', 'username', 'email', 'password', 'confirm_password'], 'required'],

            /* ---------- FIRST & LAST NAME ---------- */
            [['first_name', 'last_name'], 'string', 'min' => 2, 'max' => 50],
            [['first_name', 'last_name'], 'match', 'pattern' => '/^[A-Za-z ]+$/', 'message' => 'Only alphabets allowed.'],

            /* ---------- USERNAME VALIDATION ---------- */
            ['username', 'string', 'min' => 3, 'max' => 30],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9_]+$/', 'message' => 'Only letters, numbers and underscore allowed.'],
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'message' => 'This username has already been taken.'
            ],

            /* ---------- EMAIL VALIDATION ---------- */
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'message' => 'This email address has already been taken.'
            ],

            /* ---------- PASSWORD VALIDATION ---------- */
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            [
                'password',
                'match',
                'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*#?&]).+$/',
                'message' => 'Password must contain uppercase, lowercase, number and special character.'
            ],

            /* ---------- CONFIRM PASSWORD ---------- */
            [
                'confirm_password',
                'compare',
                'compareAttribute' => 'password',
                'message' => 'Passwords do not match.'
            ],

            /* ---------- AVATAR UPLOAD ---------- */
            [
                'avatarFile',
                'file',
                'extensions' => 'png, jpg, jpeg, webp',
                'maxSize' => 2 * 1024 * 1024,
                'skipOnEmpty' => true,
                'on' => 'upload'
            ],
        ];
    }

    /**
     * Registers a new user.
     *
     * @return User|null
     */
    public function signup()
    {
        // Stop if validation fails
        if (!$this->validate()) {
            return null;
        }

        /* ---------- CREATE USER ---------- */
        $user = new User();
        $user->first_name = $this->first_name;
        $user->last_name  = $this->last_name;
        $user->username   = $this->username;
        $user->email      = $this->email;

        // Password & auth setup
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();

        // Default values
        $user->status = User::STATUS_INACTIVE; // email verification pending
        $user->role   = User::ROLE_USER;

        /* ---------- AVATAR UPLOAD ---------- */
        $this->avatarFile = UploadedFile::getInstance($this, 'avatarFile');

        if ($this->avatarFile) {

            $fileName   = time() . '_' . $this->avatarFile->baseName . '.' . $this->avatarFile->extension;
            $uploadDir  = Yii::getAlias('@webroot/uploads/avatars/');
            $uploadPath = $uploadDir . $fileName;

            // Create directory if not exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Save file and assign to user
            if ($this->avatarFile->saveAs($uploadPath)) {
                $user->avatar = $fileName;
            }
        }

        // Save user first (needed for user ID)
        if (!$user->save()) {
            return null;
        }

        /* ---------- STRIPE CUSTOMER CREATION ---------- */
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (empty($user->stripe_customer_id)) {

                $stripeService = new StripeService();
                $customerId = $stripeService->createCustomer($user);

                $user->stripe_customer_id = $customerId;
                $user->save(false);
            }

            $transaction->commit();

        } catch (\Throwable $e) {

            $transaction->rollBack();

            Yii::error(
                'Stripe error during signup: ' . $e->getMessage(),
                __METHOD__
            );
        }

        /* ---------- SEND VERIFICATION EMAIL ---------- */
        $this->sendEmail($user);

        return $user;
    }

    /**
     * Sends email verification link.
     *
     * @param User $user
     * @return bool
     */
    public function sendEmail($user)
    {
        return Yii::$app->mailer
            ->compose(
                ['html' => 'emailVerify-html'],
                ['user' => $user]
            )
            ->setTo($this->email)
            ->setFrom([
                Yii::$app->params['supportEmail'] => Yii::$app->params['senderName']
            ])
            ->setSubject('Verify your email - ' . Yii::$app->name)
            ->send();
    }
}
