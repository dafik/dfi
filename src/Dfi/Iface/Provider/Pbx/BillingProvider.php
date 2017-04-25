<?php

namespace Dfi\Iface\Provider\Pbx;

use Criteria;
use Dfi\Iface\Provider;

/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.04.17
 * Time: 13:30
 */
interface BillingProvider extends Provider
{

    /**
     * Returns a new CcBillingQuery object.
     *
     * @param   string $modelAlias The alias of a model in the query
     * @param   BillingProvider|Criteria $criteria Optional Criteria to build the query from
     *
     * @return BillingProvider
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
     * @return BillingProvider The current query, for fluid interface
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
     * @return BillingProvider The current query, for fluid interface
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
     * @return BillingProvider The current query, for fluid interface
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
     * @return BillingProvider The current query, for fluid interface
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
     * @return BillingProvider The current query, for fluid interface
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
     * @return BillingProvider The current query, for fluid interface
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
     * @return BillingProvider The current query, for fluid interface
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
     * @return BillingProvider The current query, for fluid interface
     */
    public function filterByDuration($duration = null, $comparison = null);


}