<?
namespace Dfi\Validate;

use Criteria;
use Zend_Validate_Abstract;

class Unique extends Zend_Validate_Abstract{
  	/**
     * Validation failure message key for when the value contains non-digit characters
     */
	const NOT_UNIQUE = 'notUnique';

	/**
     * Validation failure message template definitions
     *
     * @var array
     */
	protected $_messageTemplates = array(
	  self::NOT_UNIQUE => 'notUnique'
	);
  public function __construct($model,$field)
  {
  	$this->model = ucfirst($model).'Peer';
  	$this->field = strtoupper($field);
 // 	$x = constant($this->model.'::'.$this->field);
  }
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
		$c = new Criteria();
		$c->add(constant($this->model.'::'.$this->field),$value);

		if (count(call_user_func(array($this->model,'doSelect'),$c))>0){
			$this->_error(self::NOT_UNIQUE );
			return false;
		}
		return true;
	}

}