<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 14:09
 */

namespace Dfi\Iface;


use ModelCriteria;
use PropelObjectCollection;

interface Provider
{
    /**
     * Adds a condition on a column based on a pseudo SQL clause
     * but keeps it for later use with combine()
     * Until combine() is called, the condition is not added to the query
     * Uses introspection to translate the column phpName into a fully qualified name
     * <code>
     * $c->condition('cond1', 'b.Title = ?', 'foo');
     * </code>
     *
     * @see        Criteria::add()
     *
     * @param string $conditionName A name to store the condition for a later combination with combine()
     * @param string $clause The pseudo SQL clause, e.g. 'AuthorId = ?'
     * @param mixed $value A value for the condition
     * @param mixed $bindingType A value for the condition
     *
     * @return ModelCriteria The current object, for fluid interface
     */
    public function condition($conditionName, $clause, $value = null, $bindingType = null);


    /**
     * Adds a condition on a column based on a pseudo SQL clause
     * Uses introspection to translate the column phpName into a fully qualified name
     * <code>
     * // simple clause
     * $c->where('b.Title = ?', 'foo');
     * // named conditions
     * $c->condition('cond1', 'b.Title = ?', 'foo');
     * $c->condition('cond2', 'b.ISBN = ?', 12345);
     * $c->where(array('cond1', 'cond2'), Criteria::LOGICAL_OR);
     * </code>
     *
     * @see Criteria::add()
     *
     * @param mixed $clause A string representing the pseudo SQL clause, e.g. 'Book.AuthorId = ?'
     *                           Or an array of condition names
     * @param mixed $value A value for the condition
     * @param string $bindingType
     *
     * @return ModelCriteria The current object, for fluid interface
     */
    public function where($clause, $value = null, $bindingType = null);

    public function findOne();

    /**
     * @param $array
     * @return Provider
     */
    public function select($array);

    /**
     * @return PropelObjectCollection
     */
    public function find();

    /**
     * @return Provider
     */
    public function distinct();

    public function count();

    public function getModelName();

    public function getTableMap();


}