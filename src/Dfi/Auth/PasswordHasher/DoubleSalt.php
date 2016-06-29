<?php


class Dfi_Auth_PasswordHasher_DoubleSalt implements Dfi_Auth_PasswordHasher_PasswordHasherInterface
{

    public function hash($password)
    {
        $salt = Dfi_App_Config::get('main.auth.salt');
        $hash = Dfi_App_Config::get('main.auth.hash');

        $enc = hash($hash, $salt . $password . $salt, true);

        $x = strlen($enc);

        return $enc;
    }

    public function isValid($hash, $password)
    {

        return $this->hash($password) == $hash;


    }

}