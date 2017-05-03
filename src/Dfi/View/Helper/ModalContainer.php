<?php

namespace Dfi\View\Helper;

use Zend_View_Helper_Abstract;

/**
 * Helper for rendering a pager footer.
 *
 */
class ModalContainer extends Zend_View_Helper_Abstract
{
    const OPEN = 1;
    const CLOSE = 0;

    public function modalContainer($tag = 'div', $mode = self::OPEN)
    {
        if ($mode == self::OPEN) {
            return '<' . $tag . ($this->view->makeDTid ? ' id="' . $this->view->makeDTid . '"' : '') . '>';
        } else {
            return '<' . $tag . '>';
        }
    }
}