<?php

class Dfi_View_Helper_Nav extends Zend_View_Helper_Abstract
{
    public function nav($config)
    {
        $out = '';
        if (count($config)) {
            $out .= '<h4><ul class="bread">';
            $key = 1;
            foreach ($config as $name => $entry) {
                $out .= '<li><a href="' . $entry . '">' . $name . '</a></li>';
                if ($key < count($config)) {
                    $out .= '<li>&gt;</li>';
                }
                $key++;
            }
            $out .= '</ul></h4>';
        }
        return $out;
    }
}

