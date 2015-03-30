<?php

class Dfi_View_Helper_Nav extends Zend_View_Helper_Abstract
{
    public function nav($config)
    {
        $out = '';
        if (count($config)) {
            $out .= '<ul class="breadcrumb">';
            foreach ($config as $name => $entry) {
                $out .= '<li><a href="' . $entry . '">' . $name . '</a></li>';
            }
            $out .= '</ul>';
        }
        return $out;
    }
}

