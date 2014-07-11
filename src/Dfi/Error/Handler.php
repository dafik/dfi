<?php

class Dfi_Error_Handler
{
    public static function shutdown()
    {
        $isError = false;
        if ($error = error_get_last()) {
            switch ($error['type']) {
                case E_ERROR: // 1
                case E_CORE_ERROR: // 16
                case E_COMPILE_ERROR: // 64
                case E_USER_ERROR: //256
                case E_PARSE: //4
                    $isError = true;
                    break;
                case E_WARNING: //2
                case E_NOTICE: //8
                case E_CORE_WARNING: //32
                case E_COMPILE_WARNING: //128
                case E_USER_WARNING: //512
                case E_USER_NOTICE: //1024
                case E_STRICT: //2048

                    break;
            }
        }

        if ($isError) {
            http_response_code(500);

            try {
                $e = new ErrorException($error['message'], 0, 1, $error['file'], $error['line']);
                //$guid = Dfi_Error_Report::saveException($e);
            } catch (Exception $e) {
                $guid = false;
            }


            if (!preg_match('/cli/', php_sapi_name())) {

                Zend_Registry::get('shutdownLogger')->log($error['message'] . ' : ' . $error['file'] . ' : (' . $error['line'] . ')', Zend_Log::CRIT);
                if (!Dfi_App_Config::get('main.showDebug')) {
                    $url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . '/';
                    header("Location: " . $url . "error/error" . ($guid ? '/guid/' . $guid : ''));
                    exit();
                } else {

                    ob_clean();

                    echo '<pre>REPORT: ' . ($guid ? $guid : 'brak') . "\n";
                    echo 'REQUEST: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . "\n";
                    echo 'REFERER: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '') . "\n";
                    echo 'ERROR: ' . $e->getMessage() . ' : ' . $e->getFile() . ' : (' . $e->getLine() . ')' . "\n" . $e->getTraceAsString() . '</pre>';

                }
            }
        }
    }

    public static function errorHandler($errno, $errstr, $errfile = null, $errline = null, array $errcontext = null)
    {
        $test = strpos($errfile, 'Zend' . DIRECTORY_SEPARATOR . 'Loader.php');
        if ($test === false) {
            Zend_Registry::get('errorLogger')->log($errfile . ': (' . $errline . ') : ' . $errstr, Zend_Log::ERR);
        }


    }
}