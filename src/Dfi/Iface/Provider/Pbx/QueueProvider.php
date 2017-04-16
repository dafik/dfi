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
interface QueueProvider extends Provider
{


    /**
     * @param $string
     * @return QueueProvider
     */
    public function filTerByType($string);

    /**
     * @param bool
     * @return QueueProvider
     */
    public function filterByIsActive($true);


}