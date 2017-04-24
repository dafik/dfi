<?php


namespace Dfi\Iface\Asterisk\Pbx;


use Criteria;
use Dfi\Iface\Provider;

/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.04.17
 * Time: 13:30
 */
interface ConfigProvider extends Provider
{

    /**
     * @param $getFileName
     * @param string $comparision
     * @return ConfigProvider
     */
    public function filterByFilename($getFileName, $comparision = Criteria::EQUAL);

    /**
     * @return ConfigProvider
     */
    public function orderByCategory();

    /**
     * @param $category
     * @param string $comparision
     * @return ConfigProvider
     */
    public function filterByCategory($category, $comparision = Criteria::EQUAL);

    /**
     * @return ConfigProvider
     */
    public function orderByVarMetric();

    /**
     * @return ConfigProvider
     */
    public function orderByCatMetric();
}

