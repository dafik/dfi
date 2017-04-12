<?php

interface Dfi_Auth_UserProviderInterface
{
    public function findByByLogin($login);

}