<?php

/**
 * Helper for rendering a pager footer.
 *
 */
class Dfi_View_Helper_DnSpace extends Zend_View_Helper_Abstract
{


    public function dnSpace($dn)
    {
        $parts = explode(',', $dn);
        return implode(', ', $parts);

    }


}