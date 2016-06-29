<?php
interface Dfi_Auth_PasswordHasher_PasswordHasherInterface
{

    public function hash($password);

    public function isValid($hash, $password);

}