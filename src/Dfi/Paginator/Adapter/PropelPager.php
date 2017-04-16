<?php
namespace  Dfi\Paginator\Adapter;


use PropelModelPager;
use Zend_Paginator_Adapter_Interface;

class PropelPager implements Zend_Paginator_Adapter_Interface
{
    /**
     * @var PropelModelPager
     */
    protected $_pager;

    public function __construct(PropelModelPager $pager)
    {
        $this->_pager = $pager;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        //TODO $x = 1;
        // TODO: Implement getItems() method.
        return $this->_pager->getIterator();
        //return $this->_pager->getResults();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        //TODO $x = 1;
        // TODO: Implement count() method.
        return $this->_pager->count();
    }
}