<?php

namespace frontend\models;

use yii\base\Model;
use common\models\User;

class AddressForm extends Model
{
    /* ================= USER ================= */
    public $first_name;
    public $last_name;
    public $email;
    public $username;


    /* ================= HOME ADDRESS ================= */
    public $address;
    public $city;
    public $state;
    public $pincode;

    /* ================= AVATAR ================= */
    public $avatarFile;
    public $avatar;
    public $user;

    public function rules()
    {
        return [

            [['first_name', 'last_name', 'email', 'username'], 'required'],
            [['first_name', 'last_name', 'email', 'username'], 'trim'],
            ['email', 'email'],
            ['username', 'string', 'min' => 3, 'max' => 50],

            [['address', 'city', 'state', 'pincode'], 'required'],
            [['address'], 'string'],
            [['city', 'state'], 'string', 'max' => 100],
            [['pincode'], 'string', 'min' => 4, 'max' => 10],

            [
                'avatarFile',
                'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'maxSize' => 2 * 1024 * 1024,
                'skipOnEmpty' => true,
            ],
        ];
    }

    public function validateEmailUnique($attribute)
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!$this->user) {
            return;
        }

        // Same email → OK
        if ($this->email === $this->user->email) {
            return;
        }

        $exists = User::find()
            ->where(['email' => $this->email])
            ->andWhere(['<>', 'id', $this->user->id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'This email is already used by another account.');
        }
    }

    public function Address()
    {

        if (!$this->validate()) {
            return null;
        }

        $AddressForm = new AddressForm();
        $AddressForm->first_name = $this->first_name;
        $AddressForm->last_name = $this->last_name;
        $AddressForm->email = $this->email;
        $AddressForm->username = $this->username;
        $AddressForm->address = $this->address;
        $AddressForm->city = $this->city;
        $AddressForm->state = $this->state;
        $AddressForm->pincode = $this->pincode;
        return $AddressForm;
    }
}
