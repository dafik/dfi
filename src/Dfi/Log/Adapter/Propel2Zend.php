<?php

namespace Dfi\Log\Adapter;

use BasicLogger;
use Zend_Log;

class Propel2Zend implements BasicLogger
{
    /**
     * Instance of mojavi logger
     */
    private $logger = null;

    /**
     * constructor for setting up Mojavi log adapter
     *
     * @param  Zend_Log $logger Instance of Mojavi error log obtained by
     *                               calling LogManager::getLogger();
     */
    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * A convenience function for logging an alert event.
     *
     * @param mixed $message String or Exception object containing the message to log.
     */
    public function alert($message)
    {
        $this->log($message, Zend_Log::ALERT);
    }

    /**
     * A convenience function for logging a critical event.
     *
     * @param mixed $message String or Exception object containing the message to log.
     */
    public function crit($message)
    {
        $this->log($message, Zend_Log::CRIT);
    }

    /**
     * A convenience function for logging an error event.
     *
     * @param mixed $message String or Exception object containing the message to log.
     */
    public function err($message)
    {
        $this->log($message, Zend_Log::ERR);
    }

    /**
     * A convenience function for logging a warning event.
     *
     * @param mixed $message String or Exception object containing the message to log.
     */
    public function warning($message)
    {
        $this->log($message, Zend_Log::WARN);
    }

    /**
     * A convenience function for logging an critical event.
     *
     * @param mixed $message String or Exception object containing the message to log.
     */
    public function notice($message)
    {
        $this->log($message, Zend_Log::NOTICE);
    }

    /**
     * A convenience function for logging an critical event.
     *
     * @param mixed $message String or Exception object containing the message to log.
     */
    public function info($message)
    {
        $this->log($message, Zend_Log::INFO);
    }

    /**
     * A convenience function for logging a debug event.
     *
     * @param mixed $message String or Exception object containing the message to log.
     */
    public function debug($message)
    {
        $this->log($message, Zend_Log::DEBUG);
    }

    /**
     * Primary method to handle logging.
     *
     * @param mixed $message String or Exception object containing the message to log.
     * @param integer $severity The numeric severity.  Defaults to null so that no
     *                                assumptions are made about the logging backend.
     */
    public function log($message, $severity = null)
    {
        if (is_null($this->logger)) {
            $x = 1;
            // $this->logger = LogManager::getLogger('propel');
        }

        // get a backtrace to pass class, function, file, & line to Mojavi logger
        $trace = debug_backtrace();

        // call the appropriate Mojavi logger method
        $this->logger->log($message, $severity, $trace);
    }
}
