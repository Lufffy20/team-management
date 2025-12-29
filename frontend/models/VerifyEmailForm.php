<?php

namespace frontend\models;

use yii\base\Model;
use yii\base\InvalidArgumentException;
use common\models\User;

/**
 * VerifyEmailForm model
 *
 * This model handles email verification using a verification token.
 * It supports two cases:
 * 1) New user signup email verification
 * 2) Existing user email change verification
 */
class VerifyEmailForm extends Model
{
    /**
     * Verification token received from email.
     */
    public $token;

    /**
     * User resolved from verification token.
     *
     * @var User|null
     */
    private $_user;

    /**
     * Constructor.
     * Validates token and loads related user.
     *
     * @param string $token Verification token
     * @param array  $config Model configuration
     *
     * @throws InvalidArgumentException If token is invalid or already used
     */
    public function __construct($token, array $config = [])
    {
        // Token must be a non-empty string
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }

        $this->token = $token;

        // Find user by verification token
        $this->_user = User::findOne(['verification_token' => $token]);

        // Token not found in database
        if ($this->_user === null) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }

        // Token already used (user already active and no pending email)
        if (
            $this->_user->status === User::STATUS_ACTIVE &&
            empty($this->_user->pending_email)
        ) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }

        parent::__construct($config);
    }

    /**
     * Returns resolved user instance.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Verifies the email based on token.
     *
     * @return User|null Verified user or null on failure
     *
     * @throws InvalidArgumentException If user is not found
     */
    public function verifyEmail()
    {
        $user = $this->getUser();

        if (!$user) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }

        /* =====================================================
         * CASE 1: Email update verification (profile email change)
         * ===================================================== */
        if (!empty($user->pending_email)) {

            // Move pending email to primary email
            $user->email = $user->pending_email;
            $user->pending_email = null;

            // Clear verification token
            $user->verification_token = null;

            $result = $user->save(false) ? $user : null;

            // Update Stripe customer email if customer exists
            if ($result && $user->stripe_customer_id) {
                try {
                    $stripeService = new \common\components\StripeService();
                    $stripeService->updateCustomer(
                        $user->stripe_customer_id,
                        $user->username,
                        $user->email
                    );
                } catch (\Throwable $e) {
                    // Log error but do not block verification
                    \Yii::error(
                        'Failed to update Stripe customer after email verification: ' . $e->getMessage(),
                        __METHOD__
                    );
                }
            }

            return $result;
        }

        /* ======================================
         * CASE 2: New user signup verification
         * ====================================== */

        // Activate user account
        $user->status = User::STATUS_ACTIVE;

        // Clear verification token
        $user->verification_token = null;

        return $user->save(false) ? $user : null;
    }
}
