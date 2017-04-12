<?php

/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.04.17
 * Time: 13:30
 */
interface Dfi_Asterisk_Billing_QueryInterface
{

    /**
     * Returns a new CcBillingQuery object.
     *
     * @param   string $modelAlias The alias of a model in the query
     * @param   Dfi_Asterisk_Billing_QueryInterface|Criteria $criteria Optional Criteria to build the query from
     *
     * @return Dfi_Asterisk_Billing_QueryInterface
     */
    public static function create($modelAlias = null, $criteria = null);


    public function orderByCalldate($order = Criteria::ASC);

    /**
     * Filter the query on the calldate column
     *
     * Example usage:
     * <code>
     * $query->filterByCalldate('2011-03-14'); // WHERE calldate = '2011-03-14'
     * $query->filterByCalldate('now'); // WHERE calldate = '2011-03-14'
     * $query->filterByCalldate(array('max' => 'yesterday')); // WHERE calldate < '2011-03-13'
     * </code>
     *
     * @param     mixed $calldate The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return Dfi_Asterisk_Billing_QueryInterface The current query, for fluid interface
     */
    public function filterByCalldate($calldate = null, $comparison = null);

    /**
     * Filter the query on the src column
     *
     * Example usage:
     * <code>
     * $query->filterBySrc('fooValue');   // WHERE src = 'fooValue'
     * $query->filterBySrc('%fooValue%'); // WHERE src LIKE '%fooValue%'
     * </code>
     *
     * @param     string $src The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return Dfi_Asterisk_Billing_QueryInterface The current query, for fluid interface
     */
    public function filterBySrc($src = null, $comparison = null);


    /**
     * Filter the query on the dst column
     *
     * Example usage:
     * <code>
     * $query->filterByDst('fooValue');   // WHERE dst = 'fooValue'
     * $query->filterByDst('%fooValue%'); // WHERE dst LIKE '%fooValue%'
     * </code>
     *
     * @param     string $dst The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return Dfi_Asterisk_Billing_QueryInterface The current query, for fluid interface
     */
    public function filterByDst($dst = null, $comparison = null);

    /**
     * Filter the query on the dcontext column
     *
     * Example usage:
     * <code>
     * $query->filterByDcontext('fooValue');   // WHERE dcontext = 'fooValue'
     * $query->filterByDcontext('%fooValue%'); // WHERE dcontext LIKE '%fooValue%'
     * </code>
     *
     * @param     string $dcontext The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return Dfi_Asterisk_Billing_QueryInterface The current query, for fluid interface
     */
    public function filterByDcontext($dcontext = null, $comparison = null);

    /**
     * Filter the query on the channel column
     *
     * Example usage:
     * <code>
     * $query->filterByChannel('fooValue');   // WHERE channel = 'fooValue'
     * $query->filterByChannel('%fooValue%'); // WHERE channel LIKE '%fooValue%'
     * </code>
     *
     * @param     string $channel The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return Dfi_Asterisk_Billing_QueryInterface The current query, for fluid interface
     */
    public function filterByChannel($channel = null, $comparison = null);

    /**
     * Filter the query on the dstchannel column
     *
     * Example usage:
     * <code>
     * $query->filterByDstchannel('fooValue');   // WHERE dstchannel = 'fooValue'
     * $query->filterByDstchannel('%fooValue%'); // WHERE dstchannel LIKE '%fooValue%'
     * </code>
     *
     * @param     string $dstchannel The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return Dfi_Asterisk_Billing_QueryInterface The current query, for fluid interface
     */
    public function filterByDstchannel($dstchannel = null, $comparison = null);


    /**
     * Filter the query on the disposition column
     *
     * Example usage:
     * <code>
     * $query->filterByDisposition('fooValue');   // WHERE disposition = 'fooValue'
     * $query->filterByDisposition('%fooValue%'); // WHERE disposition LIKE '%fooValue%'
     * </code>
     *
     * @param     string $disposition The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return Dfi_Asterisk_Billing_QueryInterface The current query, for fluid interface
     */
    public function filterByDisposition($disposition = null, $comparison = null);


    /**
     * Filter the query on the duration column
     *
     * Example usage:
     * <code>
     * $query->filterByDuration(1234); // WHERE duration = 1234
     * $query->filterByDuration(array(12, 34)); // WHERE duration IN (12, 34)
     * $query->filterByDuration(array('min' => 12)); // WHERE duration >= 12
     * $query->filterByDuration(array('max' => 12)); // WHERE duration <= 12
     * </code>
     *
     * @param     mixed $duration The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return CcBillingQuery The current query, for fluid interface
     */
    public function filterByDuration($duration = null, $comparison = null);


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


}