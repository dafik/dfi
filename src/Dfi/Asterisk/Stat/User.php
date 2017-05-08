<?php

namespace Dfi\Asterisk\Stat;


use Dfi\Iface\Model\Pbx\AccountSip;

class User extends ConfigAbstract
{
    const  FILE_NAME = 'users.conf';


    protected static $categoryField = 'pbx_account_sips.number';

    protected static $transTable = array(
        'pbx_account_sips.password' => 'secret',
        'pbx_account_sips.context_id' => 'context'
    );

    protected static $transTableGetters = array(
        'pbx_account_sips.password' => 'getPassword',
        'pbx_account_sips.context_id' => 'getContextName'
    );


    public function __construct($name)
    {
        self::$attributeValues = self::getConfig()['sipUser'];

        $this->filename = self::FILE_NAME;
        $this->category = $name;
    }


    public function getName()
    {
        return $this->category;
    }

    public static function create(AccountSip $trunk, $addDefaults, $doIntersect = false)
    {
        $pbxAccount = parent::create($trunk, $addDefaults);
        $pbxAccount->applyDefinitions($trunk->getDefinition());

        return $pbxAccount;
    }

    public function applyDefaults()
    {

        foreach (static::$attributeValues as $attrib => $value) {
            preg_match_all('/(__.*?__)/', $value, $matches);

            if (false !== strpos($value, '__SIP__')) {
                $value = str_replace('__SIP__', $this->getCategory(), $value);
            }


            $entry = new Entry();
            $entry->var_name = $attrib;
            $entry->var_val = $value;
            $this->addEntry($entry);


        }
    }
}