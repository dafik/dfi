<?php
namespace Dfi\Validate;


use Zend_Validate_Abstract;

class SelectOne extends Zend_Validate_Abstract
{
    /**
     * Validation failure message key for when the value contains non-digit characters
     */
    const NOT_CHOOSE_FROM = 'notChooseFrom';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_CHOOSE_FROM => "wybierz jeden z : ",
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is between min and max options, inclusively
     * if inclusive option is true.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        /*$x1 = ($value != '');
        $x2 = (strtoupper($value) != 'X');
        $x3 = ($value != 0);*/

        if ($value != '' && strtoupper($value) != 'X' && $value !== 0) {
            return true;
        }

        $this->_error(self::NOT_CHOOSE_FROM);
        return false;
    }

}
