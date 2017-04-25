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
interface AstConfigProvider extends Provider
{

    /**
     * @param $getFileName
     * @param string $comparision
     * @return AstConfigProvider
     */
    public function filterByFilename($getFileName, $comparision = Criteria::EQUAL);

    /**
     * @return AstConfigProvider
     */
    public function orderByCategory();

    /**
     * @param $category
     * @param string $comparision
     * @return AstConfigProvider
     */
    public function filterByCategory($category, $comparision = Criteria::EQUAL);

    /**
     * @return AstConfigProvider
     */
    public function orderByVarMetric();

    /**
     * @return AstConfigProvider
     */
    public function orderByCatMetric();
}

