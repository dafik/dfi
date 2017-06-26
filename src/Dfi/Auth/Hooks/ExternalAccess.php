<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 23.06.17
 * Time: 15:47
 */

namespace Dfi\Auth\Hooks;


use Dfi\Iface\Model\Sys\User;

class ExternalAccess extends HookAbstract implements HookInterface
{

    public function setOptions($options)
    {

    }

    public function isValid(User $user)
    {
        $remote = $_SERVER['REMOTE_ADDR'];
        if (preg_match('/127\.0\./', $remote)) {
            return true;
        }
        if (preg_match('/10\.[0-3]/', $remote)) {
            return true;
        }
        $this->error[] = "Dostęp zdalny zabroniony";
        return false;

    }
}