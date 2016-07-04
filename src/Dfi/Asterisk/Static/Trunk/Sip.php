<?php

class Dfi_Asterisk_Static_Trunk_Sip extends Dfi_Asterisk_Static_ConfigAbstract
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

    public static function create(PbxTrunk $trunk)
    {
        $pbxTrunk = parent::create($trunk);
        $pbxTrunk->applyDefinitions($trunk->getDefinition());
        return $pbxTrunk;
    }
}
