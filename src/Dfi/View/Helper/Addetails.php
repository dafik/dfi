<?php

/**
 * Helper for ad
 *
 */
class Dfi_View_Helper_Addetails extends Zend_View_Helper_Abstract
{
    public function addetails($value, $type)
    {

        return implode("<br />", $this->format($value, $type));

    }

    private function format($values, $type)
    {
        $type = $this->findFormat($type);
        if (!is_array($values)) {
            $tmp = $values;
            $values = array();
            $values[] = $tmp;
        }
        foreach ($values as $key => $value) {

            switch ($type) {
                case 'datetime':
                    $value = $this->formatDatetime($value);
                    break;

                case 'timestamp':
                    $value = $this->formatTimestamp($value);
                    break;
                case 'binary':
                    $value = $this->formatBinary($value);
                    break;
                case 'hide':
                    $value = false;
                    break;
                default:
                    $value = $this->view->dnSpace($value);
                    break;
            }
            $values[$key] = $value;
        }
        return $values;
    }

    private function formatDatetime($value)
    {
        if (!$value) {
            return $value;
        }

        list($datetime) = explode('.', $value);
        $date = new DateTime($datetime);

        return $date->format('Y-m-d H:i:s');
    }

    private function formatTimestamp($value)
    {
        if (!$value) {
            return $value;
        }

        $timeConverted = $this->win_time_to_unix_time($value);
        if (is_numeric($timeConverted)) {
            $date = new DateTime();
            $date->setTimestamp($timeConverted);

            return $date->format('Y-m-d H:i:s');
        }

        return $timeConverted;

    }

    private function win_time_to_unix_time($win_time)
    {
        if ($win_time == 9223372036854775807 || $win_time == 0) {
            return 'never expires';
        }


        //round the win timestamp down to seconds and remove the seconds between 1601-01-01 and 1970-01-01
        $unix_time = round($win_time / 10000000) - 11644477200;
        return $unix_time;
    }

    private function formatBinary($value)
    {
        return bin2hex($value);
    }

    private function findFormat($name)
    {
        if ($name == 'menberof') {
            $x = 1;
        }
        $types = array(
            'accountexpires' => 'timestamp',
            'badpasswordtime' => 'timestamp',
            'badpwdcount' => '',
            'cn' => '',
            'codepage' => '',
            'countrycode' => '',
            'displayname' => '',
            'distinguishedname' => '',
            'dn' => '',
            'dscorepropagationdata' => 'datetime',
            'givenname' => '',
            'instancetype' => '',
            'lastlogoff' => 'timestamp',
            'lastlogon' => 'timestamp',
            'lastlogontimestamp' => 'timestamp',
            'logoncount' => '',
            'memberof' => '',
            'name' => '',
            'objectcategory' => '',
            'objectclass' => '',
            'objectguid' => 'binary',
            'objectsid' => 'binary',
            'primarygroupid' => '',
            'pwdlastset' => 'timestamp',
            'samaccountname' => '',
            'samaccounttype' => '',
            'sn' => '',
            'telephonenumber' => '',
            'useraccountcontrol' => '',
            'userprincipalname' => '',
            'usnchanged' => '',
            'usncreated' => '',
            'whenchanged' => 'datetime',
            'whencreated' => 'datetime',
            'msexchhomeservername' => 'hide',
            'msexchmailboxguid' => 'hide',
            'msexchmailboxsecuritydescriptor' => 'hide',
            'msexchpoliciesexcluded' => 'hide',
            'msexchprevioushomemdb' => 'hide',
            'msexchrbacpolicylink' => 'hide',
            'msexchrecipientdisplaytype' => 'hide',
            'msexchrecipienttypedetails' => 'hide',
            'msexchtextmessagingstate' => 'hide',
            'msexchumdtmfmap' => 'hide',
            'msexchuseraccountcontrol' => 'hide',
            'msexchuserculture' => 'hide',
            'msexchversion' => 'hide',
            'msexchwhenmailboxcreated' => 'hide',

            'msexchhomeservername' => 'hide',
            'msexchmailboxguid' => 'hide',
            'msexchmailboxsecuritydescriptor' => 'hide',
            'msexchpoliciesexcluded' => 'hide',
            'msexchrbacpolicylink' => 'hide',
            'msexchrecipientdisplaytype' => 'hide',
            'msexchrecipienttypedetails' => 'hide',
            'msexchsafesendershash' => 'hide',
            'msexchtextmessagingstate' => 'hide',
            'msexchumdtmfmap' => 'hide',
            'msexchuseraccountcontrol' => 'hide',
            'msexchuserculture' => 'hide',
            'msexchversion' => 'hide',
            'msexchwhenmailboxcreated' => 'hide',
            'objectsid' => 'hide',
            'objectguid' => 'hide',
            'legacyexchangedn' => 'hide'

        );

        if (isset($types[$name]) && $types[$name] != '') {
            return $types[$name];
        }
        return 'default';
    }


}