<?php

/**
 * Helper for rendering a pager footer.
 *
 */
class Dfi_View_Helper_PassHide extends Zend_View_Helper_Abstract
{


    public function passHide($pass)
    {
        if (is_array($pass)) {
            array_walk_recursive($pass, function (&$value, $key) {
                if ($key == 'password') {
                    $value = $this->filter($value);
                }
            });

            return $pass;

        } else {
            return $this->filter($pass);
        }

    }


    private function filter($pass)
    {
        $len = strlen($pass);
        return substr($pass, 0, 1) . str_pad('', $len - 2, '*') . substr($pass, $len - 1);
    }
}