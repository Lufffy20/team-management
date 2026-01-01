<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use common\models\Payment;


class PaymentController extends Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Create Stripe Checkout Session
     */
    public function actionCheckout()
    {
        Stripe::setApiKey(Yii::$app->params['stripeSecretKey']);

        $user = Yii::$app->user->identity;

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',

            'customer' => $user->stripe_customer_id,

            'payment_method_types' => ['card'],

            'line_items' => [[
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => 'Team Task Subscription',
                    ],
                    'unit_amount' => 49900,
                ],
                'quantity' => 1,
            ]],

            'success_url' =>
            Yii::$app->urlManager->createAbsoluteUrl(['payment/success'])
                . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' =>
            Yii::$app->urlManager->createAbsoluteUrl(['payment/cancel']),
        ]);

        return $this->redirect($session->url);
    }



    /**
     * Payment success page
     */
    public function actionSuccess($session_id = null)
    {
        if (!$session_id) {
            throw new \yii\web\BadRequestHttpException('Session id missing');
        }

        Stripe::setApiKey(Yii::$app->params['stripeSecretKey']);

        try {
            $session = Session::retrieve($session_id);
        } catch (\Exception $e) {
            throw new \yii\web\BadRequestHttpException('Invalid Stripe session');
        }

        //Only paid payments
        if ($session->payment_status !== 'paid') {
            throw new \yii\web\BadRequestHttpException('Payment not completed');
        }

        //Duplicate entry protection
        $exists = Payment::find()
            ->where(['stripe_session_id' => $session->id])
            ->exists();

        if (!$exists) {
            $payment = new Payment();
            $payment->user_id = Yii::$app->user->id;
            $payment->stripe_session_id = $session->id;
            $payment->stripe_payment_intent = $session->payment_intent;
            $payment->amount = $session->amount_total; // cents
            $payment->currency = $session->currency;
            $payment->status = $session->payment_status;
            $payment->created_at = date('Y-m-d H:i:s');
            $payment->save(false);
        }

        return $this->render('success', [
            'session' => $session,
        ]);
    }

    public function cancel()
    {
        return $this->render('cancel');
    }
}
