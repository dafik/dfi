<?php

namespace Dfi\Asterisk\Stat\Trunk;

use Dfi\Asterisk\Stat\ConfigAbstract;
use Dfi\Iface\Model\Asterisk\Trunk;


class Sip extends ConfigAbstract
{
    const  FILE_NAME = 'sip.conf';

    protected static $categoryField = 'pbx_trunks.name';

    public function __construct($name)
    {
        $this->filename = self::FILE_NAME;
        $this->category = $name;
    }

    public function getName()
    {
        return $this->category;
    }

    public static function create(Trunk $accountSip)
    {
        $pbxTrunk = parent::create($accountSip);
        $pbxTrunk->applyDefinitions($accountSip->getDefinition());
        return $pbxTrunk;
    }
}
