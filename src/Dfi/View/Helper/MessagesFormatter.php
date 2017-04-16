<?php
namespace Dfi\View\Helper;
use Zend_View_Helper_Abstract;

/**
 * Messages helper
 *
 */

class MessagesFormatter extends Zend_View_Helper_Abstract
{
    /**
     * Formats given messages in a paragraph with given class
     * input format can be string, array, multi dimensional array
     *
     * With array use following notation: array('error', 'message')
     * -> first child is class for paragraph
     *      - use success|notice|error for blueprint
     *      - use (alert-message) warning|error|success|info for bootstrap
     * -> second child is printed message
     *
     * @param mixed  $messages A string or an array.
     * @param string $tag      (default=div)
     * @param string $format   (default=bootstrap)
     *
     * @return string
     */
    public function messagesFormatter($messages, $tag = 'div', $format = 'bootstrap')
    {
        $return = '';

        if (is_array($messages) && count($messages) > 0) {
            if (is_array($messages[0])) {
                foreach ($messages AS $msg) {
                    if (is_array($msg)) {
                        if ($format == 'bootstrap') {
                            $class = 'class="alert alert-'.$msg[0].'"';
                        } else {
                            $class = 'class="'.$msg[0].'"';
                        }
                        $return .= '<'.$tag.' '.$class.'>';
                        $return .= $msg[1];
                        $return .= '</'.$tag.'>';
                    }
                }
            } else {
                if ($format == 'bootstrap') {
                    $class = 'class="alert alert-'.$messages[0].'"';
                } else {
                    $class = 'class="'.$messages[0].'"';
                }
                $return .= '<'.$tag.' '.$class.'>';
                $return .= $messages[1];
                $return .= '</'.$tag.'>';
            }
        } else if (is_string($messages)) {
            if ($format == 'bootstrap') {
                $class = 'class="alert alert-warning"';
            } else {
                $class = 'class="notice"';
            }
            $return .= '<'.$tag.' '.$class.'>';
            $return .= $messages;
            $return .= '</'.$tag.'>';
        }

        return $return;
    }
}
