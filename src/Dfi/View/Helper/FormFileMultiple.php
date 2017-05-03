<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 03.05.17
 * Time: 16:07
 */

namespace Dfi\View\Helper;


class FormFileMultiple extends \Zend_View_Helper_FormFile
{

    public function formFileMultiple($name, $attribs = null)
    {
        $info = $this->_getInfo($name, null, $attribs);
        if (isset($attribs['multiple'])) {
            $info['multiple'] = $attribs['multiple'];
            unset($attribs['multiple']);
        } else {
            $info['multiple'] = false;
        }
        extract($info); // name, id, value, attribs, options, listsep, disable
        unset($attribs['multiple']);

        // is it disabled?
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        $mlple = '';
        if ($multiple) {
            $mlple = ' multiple';
        }

        // build the element
        $xhtml = '<input type="file"'
            . ' name="' . $this->view->escape($name) . '[]"'
            . ' id="' . $this->view->escape($id) . '"'
            . $disabled
            . $mlple
            . $this->_htmlAttribs($attribs)
            . $this->getClosingBracket();

        return $xhtml;
    }

}