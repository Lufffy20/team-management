<?php

namespace common\components;

use Yii;
use Stripe\StripeClient;
use common\models\User;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(
            Yii::$app->params['stripeSecretKey']
        );
    }

    /**
     * Create Stripe Customer
     */
    public function createCustomer(User $user): string
    {
        try {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name'  => $user->username,
                'metadata' => [
                    'user_id' => $user->id,
                    'role'    => $user->getRoleName(),
                ],
            ]);

            return $customer->id;

        } catch (\Throwable $e) {
            Yii::error(
                'Stripe createCustomer error: ' . $e->getMessage(),
                __METHOD__
            );
            throw $e;
        }
    }
}