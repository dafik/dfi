<?php

//TODO move to controller

class Dfi_Asterisk_ConnectionsFilter
{

    private $initialized = false;
    const COOKIE_SELECTOR = '_cf';


    private $options = array(

        'date_sub' => array(
            'date_from' => null,
            'date_to' => null,
            'database' => null
        ),

        'src_sub' => array(
            'src' => null,
            'src_op' => null,
            'src_show_chan' => null
        ),

        'dst_sub' => array(
            'dst' => null,
            'dst_op' => null,
            'src_show_chan' => null
        ),

        'context_sub' => array(
            'cntx' => null,
            'cntx_op' => null
        ),

        'channel_sub' => array(
            'channel' => null,
            'channel_op' => null,
            'direction' => 'src',
        ),

        'custom_sub' => array(
            'cstm' => null,
            'cstm_op' => null
        ),

        'state_sub' => array(
            'state_ANSWERED' => null,
            'state_FAILED' => null,
            'state_NO_ANSWER' => null,
            'state_BUSY' => null
        ),

        'direction_sub' => array(
            'direction' => null
        ),

        'duration_sub' => array(
            'from_val' => null,
            'from_op' => null,
            'from_un' => null,
            'to_val' => null,
            'to_op' => null,
            'to_un' => null
        )
    );


    /////////////

    public function reset()
    {
        $options = &$this->options;

        foreach ($options as $subName => $subOptions) {
            foreach ($subOptions as $elementName => $value) {
                $options[$subName][$elementName] = null;
            }
        }
    }

    public function setValues($values)
    {
        $this->reset();

        $options = &$this->options;

        foreach ($values as $subName => $subOptions) {
            foreach ($subOptions as $elementName => $value) {
                if ($value) {
                    if (array_key_exists($subName, $options) && array_key_exists($elementName, $options[$subName])) {
                        $options[$subName][$elementName] = $value;
                    } else {
                        $x = 1;
                    }
                }
            }
        }
    }

    /**
     * Singelton instance
     *
     */
    private static $_instance;


    public function __construct()
    {

        $now = new DateTime();

        $options = &$this->options;
        $options['date_sub']['date_to'] = $now->format('Y-m-d H:i:s');
        $now->modify('-1 day');
        $options['date_sub']['date_from'] = $now->format('Y-m-d H:i:s');
        $options['date_sub']['database'] = 0;

        $this->read();

    }

    public function __destruct()
    {
        $x = 1;
        $this->write();
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Singelton constructor
     *
     */
    public static function getInstance()
    {
        if (self::$_instance instanceof Dfi_Asterisk_ConnectionsFilter) {
            return self::$_instance;
        }
        return self::$_instance = new Dfi_Asterisk_ConnectionsFilter();
    }


    private function read()
    {
        try {
            $this->initialized = true;

            if (isset($_COOKIE[self::COOKIE_SELECTOR]) && $_COOKIE[self::COOKIE_SELECTOR]) {

                $base = base64_decode($_COOKIE[self::COOKIE_SELECTOR]);
                $decrypted = Dfi_Crypt_MCrypt::decode($base);

                $unserialized = unserialize($decrypted);
                $this->setValues($unserialized);


            }
        } catch (Exception $e) {

        }
    }

    /**
     * Writes self to cookie
     * @return void
     */
    public function write()
    {
        try {

            $values = $this->options;
            $valuesToSerialize = array();
            foreach ($values as $subName => $subOptions) {
                foreach ($subOptions as $elementName => $value) {
                    if ($value != null) {
                        $valuesToSerialize[$subName][$elementName] = $value;
                    }
                }
            }

            $values = serialize($valuesToSerialize);

            $crypted = Dfi_Crypt_MCrypt::encode($values);
            $base64 = base64_encode($crypted);

            $response = Zend_Controller_Front::getInstance()->getResponse();
            $response->setHeader('Set-Cookie', self::COOKIE_SELECTOR . ' = ' . $base64 . '; expires= ' . date('r', time() + 60 * 20) . ';path = /; httponly');

        } catch (Exception $e) {

        }
    }

    public function filter($isAdvancedSearch = false)
    {

        if ($this->options['date_sub']['database'] == 0) {
            $query = AstBillingQuery::create();
        } else {
            $query = AstBillingArchiveQuery::create();
        }


        $this->filterByDate($query);
        $this->filterBySource($query);
        $this->filterByDestination($query);
        if ($isAdvancedSearch) {
            $this->filterByContext($query);
            $this->filterByChannel($query);
            $this->filterByState($query);
            $this->filterByDirection($query);
            $this->filterByDuration($query);

            $this->filterByCustom($query);
        }
        return $query;
    }

    private function filterByDate(AstBillingQuery $query)
    {
        if (isset($this->options['date_sub']['date_from'])) {
            $from = new DateTime($this->options['date_sub']['date_from']);
            $query->filterByCalldate($from->format('Y-m-d H:i:s'), Criteria::GREATER_EQUAL);
        }
        if (isset($this->options['date_sub']['date_to'])) {
            $to = new DateTime($this->options['date_sub']['date_to']);
            $query->filterByCalldate($to->format('Y-m-d H:i:s'), Criteria::LESS_EQUAL);
        }
    }

    private function prepareValues($type, $values)
    {
        if ($type) {
            $val = trim($values);

            if ($type == Criteria::IN) {
                if (false !== strpos($val, ',')) {
                    $val = explode(',', $val);
                } else {
                    $val = array($val);
                }
            }
            if ($type == Criteria::ISNOTNULL || $type == Criteria::ISNULL) {
                $val = null;
            }
            return $val;
        }
        return false;
    }

    private function filterBySource(AstBillingQuery $query)
    {
        if (isset($this->options['src_sub']['src'])) {
            $op = trim($this->options['src_sub']['src_op']);
            $op = constant('Criteria::' . $op);
            $val = $this->prepareValues($op, $this->options['src_sub']['src']);

            if ($op) {
                $query->filterBySrc($val, $op);
            }
        }
    }

    private function filterByDestination(AstBillingQuery $query)
    {
        if (isset($this->options['dst_sub']['dst'])) {
            $op = trim($this->options['dst_sub']['dst_op']);
            $op = constant('Criteria::' . $op);
            $val = $this->prepareValues($op, $this->options['dst_sub']['dst']);

            if ($op) {
                $query->filterByDst($val, $op);
            }
        }
    }

    private function filterByContext(AstBillingQuery $query)
    {
        if (isset($this->options['context_sub']['context'])) {
            $op = trim($this->options['context_sub']['context_op']);
            $op = constant('Criteria::' . $op);
            $val = $this->prepareValues($op, $this->options['context_sub']['context']);

            if ($op) {
                $query->filterByDcontext($val, $op);
            }
        }

    }

    private function filterByChannel(AstBillingQuery $query)
    {
        if (isset($this->options['channel_sub']['channel'])) {
            $op = trim($this->options['channel_sub']['channel_op']);
            $op = constant('Criteria::' . $op);
            $val = $this->prepareValues($op, $this->options['channel_sub']['channel']);
            if ($op) {
                if ($this->options['channel_sub']['direction'] == 'src') {
                    $query->filterByChannel($val, $op);
                } elseif ($this->options['channel_sub']['direction'] == 'src') {
                    $query->filterByDstchannel($val, $op);
                } else {
                    $query->condition('cond1', 'Billing.Channel ' . $op . ' ?', $val);
                    $query->condition('cond2', 'Billing.Dstchannel ' . $op . ' ?', $val);
                    $query->where(array('cond1', 'cond2'), 'or');
                }
            }
        }
    }

    private function filterByState(AstBillingQuery $query)
    {
        $options = $this->options;

        if (isset($options['state_sub'])) {

            $val = array();
            isset($options['state_sub']['state_ANSWERED']) ? $val[] = 'ANSWERED' : '';
            isset($options['state_sub']['state_FAILED']) ? $val[] = 'FAILED' : '';
            isset($options['state_sub']['state_NO_ANSWER']) ? $val[] = 'NO_ANSWER' : '';
            isset($options['state_sub']['state_BUSY']) ? $val[] = 'BUSY' : '';


            if (count($val) > 0) {
                $query->filterByDisposition($val);
            }
        }
    }

    private function filterByDirection(AstBillingQuery $query)
    {

        $opt = array(
            'direction_sub' => array(
                'direction' => null
            ));
    }

    private function filterByDuration(AstBillingQuery $query)
    {
        if (isset($this->options['duration_sub']['from_val'])) {
            $opFrom = trim($this->options['duration_sub']['from_op']);
            $opFrom = constant('Criteria::' . $opFrom);
            $valFrom = $this->prepareValues($opFrom, $this->options['duration_sub']['from_val']);
            if ($this->options['duration_sub']['from_un'] == 'm') {
                $valFrom *= 60;
            }
        }
        if (isset($this->options['duration_sub']['to_val'])) {
            $opTo = trim($this->options['duration_sub']['to_op']);
            $opTo = constant('Criteria::' . $opTo);
            $valTo = $this->prepareValues($opTo, $this->options['duration_sub']['to_val']);
            if ($this->options['duration_sub']['to_un'] == 'm') {
                $valTo *= 60;
            }
        }
        if ($opFrom && $valFrom) {
            if ($opTo && $valTo) {
                $query->condition('cond1', 'Billing.Duration' . $opFrom . ' ?', $valFrom);
                $query->condition('cond2', 'Billing.Duration' . $opTo . ' ?', $valTo);
                $query->where(array('cond1', 'cond2'), 'and');
            } else {
                $query->filterByDuration($valFrom, $opFrom);
            }
        } else if ($opTo && $valTo) {
            $query->filterByDuration($valTo, $opTo);
        }


        $opt = array(
            'duration_sub' => array(
                'from_val' => null,
                'from_op' => null,
                'from_un' => null,
                'to_val' => null,
                'to_op' => null,
                'to_un' => null
            ));
    }

    private function filterByCustom(AstBillingQuery $query)
    {
        /*'source'
        'destination'
        'context'
        'channel'*/
        if (isset($this->options['custom_sub']['cstm'])) {

            $op = trim($this->options['custom_sub']['cstm_op']);
            $op = constant('Criteria::' . $op);
            $val = $this->prepareValues($op, $this->options['custom_sub']['cstm']);

            $query->condition('cond1', 'Billing.Src ' . $op . ' ?', $val);
            $query->condition('cond2', 'Billing.Dst' . $op . ' ?', $val);
            $query->condition('cond3', 'Billing.Dcontext ' . $op . ' ?', $val);
            $query->condition('cond4', 'Billing.Channel ' . $op . ' ?', $val);
            $query->condition('cond5', 'Billing.Dstchannel ' . $op . ' ?', $val);

            if (preg_match('/NOT/', $op)) {
                $query->where(array('cond1', 'cond2', 'cond3', 'cond4', 'cond5'), 'and');
            } else {
                $query->where(array('cond1', 'cond2', 'cond3', 'cond4', 'cond5'), 'or');
            }
        }
    }
}