<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 23.06.17
 * Time: 15:46
 */

namespace Dfi\Auth\Hooks;


use Dfi\Iface\Model\Sys\User;

interface HookInterface
{
    const LEVEL_ERROR = -1;
    const LEVEL_WARNING = 0;

    public function isValid(User $user);
}