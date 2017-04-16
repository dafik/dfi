<?php
namespace Dfi\View\Helper;

use Zend_View_Helper_Abstract;

class Nav extends Zend_View_Helper_Abstract
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

