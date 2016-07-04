<?php

class Dfi_Asterisk_Static_User extends Dfi_Asterisk_Static_ConfigAbstract
{

    const  FILE_NAME = 'users.conf';
    protected static $attributes = [];

    /**
     * Return asterisk user by given sip number
     * @param string $sip
     * @return Dfi_Asterisk_Static_User
     */
    public static function retrieveBySip($sip)
    {

        $pdo = self::getPdo();
        $query = 'Select * From ast_config Where `filename` = \'' . self::FILE_NAME . '\' AND `category` = ' . $sip . ' ORDER BY var_metric';

        $stmt = $pdo->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $user = new Dfi_Asterisk_Static_User();
            foreach ($rows as $row) {
                $entry = new Dfi_Asterisk_Static_UserEntry($row);
                $user->addEntry($entry);
            }

            return $user;
        }
        return false;
    }

    public static function getSips()
    {
        $sql = 'SELECT distinct(a.`category`) FROM ast_config a WHERE a.filename=\'' . self::FILE_NAME . '\'  ORDER BY a.`category`';
        $stmt = self::getPdo()->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    public function addEntry(Dfi_Asterisk_Static_UserEntry $entry)
    {
        $check = $this->checkEntry($entry);
        if (!$check) {
            $this->prepareEntry($entry);
        }
        $this->entries[$entry->var_name] = $entry;
        $this->prepareUser($entry);
    }


    public function deleteEntry($entry)
    {
        if (!$entry instanceof Dfi_Asterisk_Static_UserEntry) {
            if (!isset($this->entries[$entry])) {
                return;
                //throw new Exception('entry :' . $entry . ' not found');
            }
            $entry = $this->entries[$entry];
        }
        $entry->delete();
    }


    private function prepareUser(Dfi_Asterisk_Static_UserEntry $entry)
    {

        if (null === $this->cat_metric) {
            $this->cat_metric = $entry->cat_metric;
        } elseif ($this->cat_metric != $entry->cat_metric) {
            throw new Exception('cat_metric mismatch');
        }

        if (null === $this->var_metric) {
            $this->var_metric = $entry->var_metric;
        } elseif ($this->var_metric < $entry->var_metric) {
            $this->var_metric = $entry->var_metric;
        }
        if (null === $this->commented) {
            $this->commented = 0;
        }
        if (null === $this->category) {
            $this->category = $entry->category;
        } elseif ($this->category != $entry->category) {
            throw new Exception('category mismatch');
        }

    }

    private function prepareEntry(Dfi_Asterisk_Static_UserEntry $entry)
    {
        if ($this->cat_metric) {
            $entry->cat_metric = $this->cat_metric;
        } else {
            $sqlMax = 'SELECT IFNULL(max(a.`cat_metric`),0) + 1 FROM ast_config a';
            $stmt = $this->getPdo()->query($sqlMax);

            $maxCatMetric = $stmt->fetchColumn(0);
            $entry->cat_metric = $maxCatMetric;
        }
        if ($this->var_metric || $this->var_metric === 0) {
            $entry->var_metric = $this->var_metric + 1;
        } else {
            $entry->var_metric = 0;
        }
        $entry->commented = $this->commented;
        $entry->filename = self::FILE_NAME;
        $entry->category = $this->category;

        if (!in_array($entry->var_name, static::$attributes)) {
            throw new Exception('unknown attribute: ' . $entry->var_name);
        }
        if (null === $entry->var_val) {
            throw new Exception('value can\'t be null');
        }


    }

    private function checkEntry(Dfi_Asterisk_Static_UserEntry $entry)
    {

        $requiredProperties = array('cat_metric', 'var_metric', 'commented', 'filename', 'category', 'var_name', 'var_val');
        foreach ($requiredProperties as $property) {
            if (null === $entry->$property) {
                return false;
            }
        }
        return true;
    }

    public static function getPdo()
    {
        return Propel::getConnection();
    }


    public function setSipNumber($sip)
    {
        $this->category = $sip;
    }


    public function getSipNumber()
    {
        return $this->category;
    }

    public function setDdiNumber($ddiNumber)
    {
        $entry = $this->getSipEntry('cid_number');

        if ($ddiNumber) {
            if (!$entry) {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'cid_number';
                $entry->var_val = $ddiNumber;

                $this->addEntry($entry);
                $this->isModified = true;
            }
            if ($ddiNumber != $entry->var_val) {
                $entry->var_val = $ddiNumber;
                $entry->isModified = true;
                $this->isModified = true;
            }
        }
        if ($entry) {
            $entry->delete();
        }
    }

    public function getSipVariable($variable)
    {
        if (isset($this->entries[$variable])) {
            return $this->entries[$variable]->var_val;
        }

        return false;
    }

    /**
     * Enter description here ...
     * @param string $variable
     * @return Dfi_Asterisk_Static_UserEntry|boolean
     */
    public function getSipEntry($variable)
    {
        if (isset($this->entries[$variable])) {
            return $this->entries[$variable];
        }
        return false;
    }

    public static function create($number, $password, $context)
    {

        self::prepareAttributes();

        $attribs = self::getConfig()['sipUser'];


        $user = new Dfi_Asterisk_Static_User();
        $user->setSipNumber($number);

        foreach ($attribs as $attrib => $value) {
            if (strpos($value, '__SIP__')) {
                $value = str_replace('__SIP__', $number, $value);
            }
            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = $attrib;
            $entry->var_val = $value;

            $user->addEntry($entry);
        }

        $entry = new Dfi_Asterisk_Static_UserEntry();
        $entry->var_name = 'secret';
        $entry->var_val = $password;

        $user->addEntry($entry);

        $entry = new Dfi_Asterisk_Static_UserEntry();
        $entry->var_name = 'context';
        $entry->var_val = $context;

        $user->addEntry($entry);


        return $user;
    }


    public function modify($password = null, $context = null, $definition)
    {
        $passwordE = $this->getSipVariable('secret');

        if ($passwordE != $password) {
            $entry = $this->getSipEntry('secret');
            $entry->var_val = $password;
            $entry->isModified = true;
            $this->isModified = true;
        }

        $contextE = $this->getSipVariable('context');

        if ($contextE != $context) {
            $entry = $this->getSipEntry('context');
            $entry->var_val = $context;
            $entry->isModified = true;
            $this->isModified = true;
        }
        $this->applyDefinitions($definition);
    }

    public function delete()
    {
        /** @var $entry Dfi_Asterisk_Static_UserEntry */
        foreach ($this->entries as $entry) {
            $entry->delete();
        }
    }

    public function save($reloadAsterisk = true)
    {
        /** @var $entry Dfi_Asterisk_Static_UserEntry */
        foreach ($this->entries as $entry) {
            $res = $entry->save($this->getPdo());
            if ($res) {
                $this->isModified = true;
            }
        }
        if ($this->isModified && $reloadAsterisk) {
            Dfi_Asterisk_Ami::reload();
        }


    }

    /**
     * Enter description here ...
     * @param string $number
     * @param string $context
     * @param bool $isWebRtc
     * @return Dfi_Asterisk_Static_User
     */

    public function getDefinition()
    {
        $tmp = [];
        /** @var Dfi_Asterisk_Static_UserEntry $entry */
        foreach ($this->entries as $entry) {
            $tmp[] = $entry->var_name . '=' . $entry->var_val;
        }
        return $tmp;
    }


    public static function prepareAttributes()
    {
        if (count(self::$attributes) == 0) {
            $class = static::class;
            $file = constant($class . '::FILE_NAME');

            $config = new Zend_Config_Ini('configs/ini/configs.ini', APPLICATION_ENV);
            $config = $config->toArray();

            $attr = array_merge($config['sip']['entry'], $config['users']['general']);


            self::$attributes = array_keys($attr);
            sort(self::$attributes);
        }
    }


}