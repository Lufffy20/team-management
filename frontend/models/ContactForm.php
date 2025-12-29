<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm model
 *
 * This model is used for the frontend contact form.
 * It handles validation and email sending logic.
 */
class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
    public $verifyCode;

    /**
     * Validation rules for contact form fields.
     */
    public function rules()
    {
        return [
            // Name, email, subject, and message are required
            [['name', 'email', 'subject', 'body'], 'required'],

            // Email must be a valid email address
            ['email', 'email'],

            // CAPTCHA verification
            ['verifyCode', 'captcha'],
        ];
    }

    /**
     * Custom attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

    /**
     * Sends an email using contact form data.
     *
     * @param string $email Target email address
     * @return bool Whether the email was sent successfully
     */
    public function sendEmail($email)
    {
        return Yii::$app->mailer
            ->compose()
            ->setTo($email)
            ->setFrom([
                Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']
            ])
            ->setReplyTo([$this->email => $this->name])
            ->setSubject($this->subject)
            ->setTextBody($this->body)
            ->send();
    }
}
