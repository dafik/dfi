<?
namespace Dfi\DataTable\Request;

use Criteria;

class Order
{
    private $column;
    private $direction;


    public function __construct($column, $direction)
    {
        $this->column = $column;
        if ($direction == strtolower('asc')) {
            $direction = Criteria::ASC;
        } else {
            $direction = Criteria::DESC;
        }
        $this->direction = $direction;

    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }


}