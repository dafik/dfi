<?php

interface Dfi_Auth_UserInterface
{
    /**
     * @return boolean
     */
    public function getActive();

    /**
     * @return Dfi_Auth_Adapter_AdapterInterface
     */
    public function getAuthAdapter();

}