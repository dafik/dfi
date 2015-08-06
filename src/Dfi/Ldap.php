<?php

class Dfi_Ldap extends Zend_Ldap
{

    const TIMEOUT = 5;
    const NETWORK_TIMEOUT = 2;

    protected $_connectString;


    public function connect($host = null, $port = null, $useSsl = null, $useStartTls = null)
    {
        if ($host === null) {
            $host = $this->_getHost();
        }
        if ($port === null) {
            $port = $this->_getPort();
        } else {
            $port = (int)$port;
        }
        if ($useSsl === null) {
            $useSsl = $this->_getUseSsl();
        } else {
            $useSsl = (bool)$useSsl;
        }
        if ($useStartTls === null) {
            $useStartTls = $this->_getUseStartTls();
        } else {
            $useStartTls = (bool)$useStartTls;
        }

        if (!$host) {
            /**
             * @see Zend_Ldap_Exception
             */
            require_once 'Zend/Ldap/Exception.php';
            throw new Zend_Ldap_Exception(null, 'A host parameter is required');
        }

        $useUri = false;
        /* Because ldap_connect doesn't really try to connect, any connect error
         * will actually occur during the ldap_bind call. Therefore, we save the
         * connect string here for reporting it in error handling in bind().
         */
        $hosts = array();
        if (preg_match_all('~ldap(?:i|s)?://~', $host, $hosts, PREG_SET_ORDER) > 0) {
            $this->_connectString = $host;
            $useUri = true;
            $useSsl = false;
        } else {
            if ($useSsl) {
                $this->_connectString = 'ldaps://' . $host;
                $useUri = true;
            } else {
                $this->_connectString = 'ldap://' . $host;
            }
            if ($port) {
                $this->_connectString .= ':' . $port;
            }
        }

        $this->disconnect();

        /* Only OpenLDAP 2.2 + supports URLs so if SSL is not requested, just
         * use the old form.
         */
        $resource = ($useUri) ? @ldap_connect($this->_connectString) : @ldap_connect($host, $port);

        if (is_resource($resource) === true) {
            ldap_set_option($this->_resource, LDAP_OPT_NETWORK_TIMEOUT, self::NETWORK_TIMEOUT);
            ldap_set_option($this->_resource, LDAP_OPT_TIMELIMIT, self::TIMEOUT);

            $this->_resource = $resource;
            $this->_boundUser = false;

            $optReferrals = ($this->_getOptReferrals()) ? 1 : 0;
            if (@ldap_set_option($resource, LDAP_OPT_PROTOCOL_VERSION, 3) &&
                @ldap_set_option($resource, LDAP_OPT_REFERRALS, $optReferrals)
            ) {
                if ($useSsl || !$useStartTls || @ldap_start_tls($resource)) {
                    return $this;
                }
            }

            /**
             * @see Zend_Ldap_Exception
             */
            require_once 'Zend/Ldap/Exception.php';
            $zle = new Zend_Ldap_Exception($this, "$host:$port");
            $this->disconnect();
            throw $zle;
        }
        /**
         * @see Zend_Ldap_Exception
         */
        require_once 'Zend/Ldap/Exception.php';
        throw new Zend_Ldap_Exception(null, "Failed to connect to LDAP server: $host:$port");
    }


}