<?php

namespace Dfi\Auth\Adapter\PasswordHasher;


use Dfi\App\Config;

class DoubleSalt implements PasswordHasherInterface
{

    public function hash($password)
    {
        $salt = Config::get('main.auth.salt');
        $hash = Config::get('main.auth.hash');

        $enc = hash($hash, $salt . $password . $salt, true);

        $x = strlen($enc);

        return $enc;
    }

    public function isValid($hash, $password)
    {

        return $this->hash($password) == $hash;


    }

}