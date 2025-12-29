<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use common\models\User;
use common\components\StripeService;


class SignupForm extends Model
{
    public $first_name;
    public $last_name;
    public $username;
    public $email;
    public $password;
    public $confirm_password;

    // new field that is not exist in db
    public $avatarFile;

    public function rules()
    {
        return [

            /* ----- BASIC SANITIZATION ----- */
            [['first_name', 'last_name', 'username', 'email'], 'trim'],

            /* ----- REQUIRED FIELDS ----- */
            [['first_name', 'last_name', 'username', 'email', 'password', 'confirm_password'], 'required'],

            /* ----- FIRST NAME / LAST NAME ----- */
            [['first_name', 'last_name'], 'string', 'min' => 2, 'max' => 50],
            [['first_name', 'last_name'], 'match', 'pattern' => '/^[A-Za-z ]+$/', 'message' => 'Only alphabets allowed.'],

            /* ----- USERNAME VALIDATION ----- */
            ['username', 'string', 'min' => 3, 'max' => 30],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9_]+$/', 'message' => 'Only letters, numbers & underscore allowed.'],
            ['username', 'unique', 'targetClass' => '\common\models\User',
            'message' => 'This username has already been taken.'
            ],


            /* ----- EMAIL VALIDATION ----- */
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User',
            'message' => 'This email address has already been taken.'
            ],


            /* ----- PASSWORD VALIDATION ----- */
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            ['password', 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*#?&]).+$/',
                'message' => 'Password must contain uppercase, lowercase, number & special character.'
            ],

            /* ----- CONFIRM PASSWORD ----- */
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],

            /* ----- NEW: AVATAR FILE VALIDATION ----- */
            ['avatarFile', 'file',
                'extensions' => 'png, jpg, jpeg, webp',
                'maxSize' => 2 * 1024 * 1024,
                'skipOnEmpty' => true,
                'on' => 'upload'
            ],
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->first_name = $this->first_name;
        $user->last_name = $this->last_name;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->status = User::STATUS_INACTIVE;
        $user->role = User::ROLE_USER;


        // Avatar upload
        $this->avatarFile = UploadedFile::getInstance($this, 'avatarFile');

        if ($this->avatarFile) {

            $fileName = time() . '_' . $this->avatarFile->baseName . '.' . $this->avatarFile->extension;
            $uploadPath = Yii::getAlias('@webroot/uploads/avatars/' . $fileName);

            if (!is_dir(Yii::getAlias('@webroot/uploads/avatars'))) {
                mkdir(Yii::getAlias('@webroot/uploads/avatars'), 0777, true);
            }

            if ($this->avatarFile->saveAs($uploadPath)) {
                $user->avatar = $fileName;
            }
        }

        // save user first bcz user id
        if (!$user->save()) {
            return null;
        }

    $transaction = Yii::$app->db->beginTransaction();

    try {
        if (empty($user->stripe_customer_id)) {

            //object use contstractor
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
//email always send
$this->sendEmail($user);
return $user;


        return null;
    }

    public function sendEmail($user)
    {
        return Yii::$app->mailer
            ->compose(
                ['html' => 'emailVerify-html'],
                ['user' => $user]
            )
            ->setTo($this->email)
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['senderName']])
            ->setSubject('Verify your email - ' . Yii::$app->name)
            ->send();
    }
}
