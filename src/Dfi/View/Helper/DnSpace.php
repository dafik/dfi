<?php
namespace Dfi\View\Helper;
use Zend_View_Helper_Abstract;

/**
 * Helper for rendering a pager footer.
 *
 */
class DnSpace extends Zend_View_Helper_Abstract
{


    public function dnSpace($dn)
    {
        $parts = explode(',', $dn);
        return implode(', ', $parts);

    }


}