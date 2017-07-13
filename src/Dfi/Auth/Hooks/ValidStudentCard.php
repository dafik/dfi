<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 23.06.17
 * Time: 15:47
 */

namespace Dfi\Auth\Hooks;


use Dfi\Iface\Model\Sys\User;

class ValidStudentCard extends HookAbstract implements HookInterface
{
    protected $url = "";

    public function setOptions($options)
    {
        if (isset($options['url'])) {
            $this->url = $options['url'];
        }
    }

    public function isValid(User $user)
    {
        $url = $this->url . $user->getLogin();

        $client = new \Zend_Http_Client($url);

        $response = $client->request();
        /** @var ValidStudentCardResponse $m */
        $m = json_decode($response->getBody());

        $result = true;

        if ($m->isError) {
            $this->error[] = $m->message ? $m->message : 'undefined';
            $result = false;
        }


        if ($m->isStudent == true) {
            if (!$m->hasSchoolId) {
                $this->error[] = 'brak legitymacji';
                $result = false;

            } else if (!$m->hasSchoolIdDate) {
                $this->error[] = 'brak daty legitymacji';
                $result = false;
            } else if (!$m->hasValidSchoolIdDate) {
                $this->error[] = 'brak ważnej legitymacji';
                $result = false;
            }
        } else {
            return true;
        }
        if ($result) {
            return true;
        } else {
            if ($this->level == ValidContract::LEVEL_WARNING) {
                return true;
            } else {
                return false;
            }
        }

    }
}

class  ValidStudentCardResponse
{
    /**
     * @var bool
     */
    public $isStudent;

    /**
     * @var bool
     */
    public $hasSchoolId;

    /**
     * @var bool
     */
    public $hasSchoolIdDate;

    /**
     * @var bool
     */
    public $hasValidSchoolIdDate;

    /**
     * @var bool
     */
    public $isError;

    /**
     * @var  [string]
     */
    public $message;
}