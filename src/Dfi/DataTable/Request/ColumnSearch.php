<?
namespace Dfi\DataTable\Request;

use Criteria;

class ColumnSearch
{
    /**
     * @var boolean
     */
    private $regex;
    /**
     * @var string
     */
    private $value;

    private $operator = Criteria::EQUAL;

    public function __construct($regex, $value)
    {
        $this->regex = filter_var($regex, FILTER_VALIDATE_BOOLEAN);
        $this->value = $value;
        if (false !== strpos($value, '%')) {
            $this->operator = Criteria::LIKE;
        }

    }

    public function hasSearch()
    {
        return $this->value != '';
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getOperator()
    {
        return $this->operator;
    }


}