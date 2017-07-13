<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 23.06.17
 * Time: 15:47
 */

namespace Dfi\Auth\Hooks;


use Dfi\Iface\Model\Sys\User;

class ValidContract extends HookAbstract implements HookInterface
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
        //$url = $this->url . "d.prowadzisz";


        $client = new \Zend_Http_Client($url, ['adapter' => 'Zend_Http_Client_Adapter_Curl']);

        $response = $client->request();
        $m = json_decode($response->getBody());

        if ($m->hasContract == true) {
            $validUntil = date_create($m->validUntil);
            if ($validUntil) {
                $validUntil->modify("-7 days");
                //$validUntil->modify("-7 years");
                $now = new \DateTime();
                if ($now >= $validUntil) {
                    $this->warning[] = "Umowa kończy sie $m->validUntil";
                }
            }
            return true;
        } else {
            $this->error[] = "Brak umowy";
        }

        if ($this->level == ValidContract::LEVEL_WARNING) {
            return true;
        } else {
            return false;
        }

    }
}