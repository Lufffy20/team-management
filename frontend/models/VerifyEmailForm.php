<?php

namespace frontend\models;

use common\models\User;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmailForm extends Model
{
    public $token;
    private $_user;

    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }

        $this->token = $token;
        $this->_user = User::findOne(['verification_token' => $token]);

        // ❌ Token not found
        if ($this->_user === null) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }

        // ❌ ALREADY ACTIVATED USER (THIS FIXES YOUR FAILING TEST)
        if ($this->_user->status === User::STATUS_ACTIVE && empty($this->_user->pending_email)) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }

        parent::__construct($config);
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function verifyEmail()
    {
        $user = $this->getUser();

        if (!$user) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }

        /**
         * 🔥 CASE 1 → Email update verification (profile email change)
         */
        if (!empty($user->pending_email)) {
            $user->email = $user->pending_email;
            $user->pending_email = null;
            $user->verification_token = null;

            $result = $user->save(false) ? $user : null;

            // Update Stripe customer if customer exists
            if ($result && $user->stripe_customer_id) {
                try {
                    $stripeService = new \common\components\StripeService();
                    $stripeService->updateCustomer(
                        $user->stripe_customer_id,
                        $user->username,
                        $user->email
                    );
                } catch (\Throwable $e) {
                    \Yii::error(
                        'Failed to update Stripe customer after email verification in VerifyEmailForm: ' . $e->getMessage(),
                        __METHOD__
                    );
                    // Don't throw the exception as it shouldn't prevent the email verification
                }
            }

            return $result;
        }

        /**
         * 🔥 CASE 2 → Normal signup verification
         */
        $user->status = User::STATUS_ACTIVE;
        $user->verification_token = null;

        return $user->save(false) ? $user : null;
    }
}
