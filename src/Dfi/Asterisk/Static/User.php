<?php

class Dfi_Asterisk_Static_User
{
    private $entries = array();
    private static $pdo;

    private $cat_metric;
    private $var_metric;
    private $commented = 0;
    const  FILE_NAME = 'users.conf';
    private $category;


    private $isModified = false;

    private $attributes = array(
        'fullname',
        'callerid',
        'secret',
        'hasvoicemail',
        'hassip',
        'context',
        'host',
        'transfer',
        'canpark',
        'cancallforward',
        'disallow',
        'allow',
        'callreturn',
        'callcounter',
        'qualify',
        'cid_number',
        'deny',
        'permit',
        'call-limit',
        'busylevel',

        'transport',
        'avpf',
        'icesupport',
        'nat',
        'encryption',

        'dtlsenable',
        'dtlsverify',
        'dtlscertfile',
        'dtlsprivatekey',
        'dtlscafile',
        'dtlssetup',
        'force_avp'


    );


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

        $user = new Dfi_Asterisk_Static_User();
        foreach ($rows as $row) {
            $entry = new Dfi_Asterisk_Static_UserEntry($row);
            $user->addEntry($entry);
        }

        return $user;
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

        if (!in_array($entry->var_name, $this->attributes)) {
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

    private static function getConfig()
    {
        $config = new Zend_Config_Ini('configs/asterisk.ini', APPLICATION_ENV);
        return $config->toArray();
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

    public function modify($password = null, $context = null, $ddi = null, $isWebRtc = false)
    {
        $passwordE = $this->getSipVariable('secret');
        if ($passwordE && $password) {
            if ($passwordE != $password) {
                $entry = $this->getSipEntry('secret');
                $entry->var_val = $password;
                $entry->isModified = true;
                $this->isModified = true;
            }
        }
        $contextE = $this->getSipVariable('context');
        if ($contextE && $context) {
            if ($contextE != $context) {
                $entry = $this->getSipEntry('context');
                $entry->var_val = $context;
                $entry->isModified = true;
                $this->isModified = true;
            }
        }
        $ddiE = $this->getSipVariable('cid_number');
        if ($ddiE) {
            if ($ddiE != $ddi) {
                if ($ddi) {
                    $entry = $this->getSipEntry('cid_number');
                    $entry->var_val = $ddi;
                    $entry->isModified = true;
                    $this->isModified = true;
                } else {
                    $entry = $this->getSipEntry('cid_number');
                    $entry->delete();
                    $this->isModified = true;
                }
            }
        } elseif ($ddi) {
            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'cid_number';
            $entry->var_val = $ddi;
            $this->addEntry($entry);
        }

        if ($isWebRtc) {
            $transport = $this->getSipVariable('transport');
            if ($transport) {
                $entry = $this->getSipEntry('transport');
                $entry->var_val = 'udp,ws,wss';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'transport';
                $entry->var_val = 'udp,ws,wss';
                $this->addEntry($entry);
            }
            $avpf = $this->getSipVariable('avpf');
            if ($avpf) {
                $entry = $this->getSipEntry('avpf');
                $entry->var_val = 'yes';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'avpf';
                $entry->var_val = 'yes';
                $this->addEntry($entry);
            }
            $ice = $this->getSipVariable('icesupport');
            if ($ice) {
                $entry = $this->getSipEntry('icesupport');
                $entry->var_val = 'yes';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'icesupport';
                $entry->var_val = 'yes';
                $this->addEntry($entry);
            }
            $nat = $this->getSipVariable('nat');
            if ($nat) {
                $entry = $this->getSipEntry('nat');
                $entry->var_val = 'force_rport,comedia';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'nat';
                $entry->var_val = 'force_rport,comedia';
                $this->addEntry($entry);
            }
            $enc = $this->getSipVariable('encryption');
            if ($enc) {
                $entry = $this->getSipEntry('encryption');
                $entry->var_val = 'no';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'encryption';
                $entry->var_val = 'no';
                $this->addEntry($entry);
            }
            ////
            $dtlsenable = $this->getSipVariable('dtlsenable');
            if ($dtlsenable) {
                $entry = $this->getSipEntry('dtlsenable');
                $entry->var_val = 'yes';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'dtlsenable';
                $entry->var_val = 'yes';
                $this->addEntry($entry);
            }

            $dtlsverify = $this->getSipVariable('dtlsverify');
            if ($dtlsverify) {
                $entry = $this->getSipEntry('dtlsverify');
                $entry->var_val = 'no';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'dtlsverify';
                $entry->var_val = 'no';
                $this->addEntry($entry);
            }
            $dtlscertfile = $this->getSipVariable('dtlscertfile');
            if ($dtlscertfile) {
                $entry = $this->getSipEntry('dtlscertfile');
                $entry->var_val = '/etc/asterisk/keys/crt.pem';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'dtlscertfile';
                $entry->var_val = '/etc/asterisk/keys/crt.pem';
                $this->addEntry($entry);
            }
            $dtlsprivatekey = $this->getSipVariable('dtlsprivatekey');
            if ($dtlsprivatekey) {
                $entry = $this->getSipEntry('dtlsprivatekey');
                $entry->var_val = '/etc/asterisk/keys/key.pem';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'dtlsprivatekey';
                $entry->var_val = '/etc/asterisk/keys/key.pem';
                $this->addEntry($entry);
            }

            $dtlscafile = $this->getSipVariable('dtlscafile');
            if ($dtlscafile) {
                $entry = $this->getSipEntry('dtlscafile');
                $entry->var_val = '/etc/asterisk/keys/ca-crt.pem';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'dtlscafile';
                $entry->var_val = '/etc/asterisk/keys/ca-crt.pem';
                $this->addEntry($entry);
            }

            $dtlssetup = $this->getSipVariable('dtlssetup');
            if ($dtlssetup) {
                $entry = $this->getSipEntry('dtlssetup');
                $entry->var_val = 'actpass';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'dtlssetup';
                $entry->var_val = 'actpass';
                $this->addEntry($entry);
            }


            $force_avp = $this->getSipVariable('force_avp');
            if ($force_avp) {
                $entry = $this->getSipEntry('force_avp');
                $entry->var_val = 'yes';
                $entry->isModified = true;
                $this->isModified = true;
            } else {
                $entry = new Dfi_Asterisk_Static_UserEntry();
                $entry->var_name = 'force_avp';
                $entry->var_val = 'yes';
                $this->addEntry($entry);
            }


        } else {
            $this->deleteEntry('transport');
            $this->deleteEntry('avpf');
            $this->deleteEntry('icesupport');
            $this->deleteEntry('nat');
            $this->deleteEntry('encryption');

            //
            $this->deleteEntry('dtlsenable');
            $this->deleteEntry('dtlsverify');
            $this->deleteEntry('dtlscertfile');
            $this->deleteEntry('dtlsprivatekey');
            $this->deleteEntry('dtlscafile');
            $this->deleteEntry('dtlssetup');
            $this->deleteEntry('force_avp');
        }
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
    public static function create($number, $context, $isWebRtc = false)
    {

        $attribs = self::getConfig()['sip'];


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
        $entry->var_val = trim(shell_exec('pwgen 7 1'));

        $user->addEntry($entry);

        $entry = new Dfi_Asterisk_Static_UserEntry();
        $entry->var_name = 'context';
        $entry->var_val = $context;

        $user->addEntry($entry);


        if ($isWebRtc) {

            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'transport';
            $entry->var_val = 'udp,ws,wss';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'avpf';
            $entry->var_val = 'yes';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'icesupport';
            $entry->var_val = 'yes';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'nat';
            $entry->var_val = 'force_rport,comedia';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'encryption';
            $entry->var_val = 'no';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'dtlsenable';
            $entry->var_val = 'yes';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'dtlsverify';
            $entry->var_val = 'no';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'dtlscertfile';
            $entry->var_val = '/etc/asterisk/keys/crt.pem';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'dtlsprivatekey';
            $entry->var_val = '/etc/asterisk/keys/key.pem';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'dtlscafile';
            $entry->var_val = '/etc/asterisk/keys/ca-crt.pem';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'dtlssetup';
            $entry->var_val = 'actpass';
            $user->addEntry($entry);


            $entry = new Dfi_Asterisk_Static_UserEntry();
            $entry->var_name = 'force_avp';
            $entry->var_val = 'yes';
            $user->addEntry($entry);


        }
        return $user;
    }
}