<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 23.06.17
 * Time: 15:47
 */

namespace Dfi\Auth\Hooks;


use Dfi\Iface\Model\Sys\User;

abstract class HookAbstract implements HookInterface
{

    protected $level = self::LEVEL_ERROR;
    protected $message;
    protected $warning;
    protected $error;

    private $filterValue;
    private $filterBy;

    public function setOptions($options)
    {
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function setFilterBy($filterField)
    {
        $this->filterBy = $filterField;
    }

    public function setFilterValue($filterValue)
    {
        $this->filterValue = $filterValue;
    }


    public function isValid(User $user)
    {
        return false;
    }

    public function match(User $user)
    {
        if ($this->filterBy) {
            $method = 'get' . ucfirst($this->filterBy);
            if (method_exists($user, $method)) {
                if ($user->$method() == $this->filterValue) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    public function getMessages()
    {
        return array_merge($this->error, $this->warning);
    }

    public function getErrors()
    {
        return $this->error;
    }

    public function getWarnings()
    {
        return $this->warning;
    }
}