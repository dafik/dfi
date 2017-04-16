<?php

namespace Dfi\Asterisk\Stat;

use Criteria;
use Dfi\Iface\Helper;
use Dfi\Iface\Model\Pbx\AstConfig;
use Dfi\Iface\Provider\Pbx\AstConfigProvider;

class Generic extends ConfigAbstract
{
    public function __construct($config, $name, $catMetric = null)
    {
        parent::__construct();

        $this->filename = $config;
        $this->category = $name;
        if (null !== $catMetric) {
            $this->cat_metric = $catMetric;
        }
    }


    /**
     * @param $category string
     * @return ConfigAbstract
     */
    public static function retrieveByConfigAndCategory($config, $category)
    {

        $providerName = Helper::getClass("iface.provider.pbx.astConfig");
        /** @var AstConfigProvider $provider */
        $provider = $providerName::create();

        $entries = $provider
            ->filterByFilename($config)
            ->filterByCategory($category)
            ->orderByVarMetric()
            ->find();

        if ($entries->count() > 0) {
            $class = get_called_class();

            /** @var $astConfigObj ConfigAbstract */
            $astConfigObj = new self($config, $category);
            foreach ($entries as $row) {
                $entry = new Entry($row);
                $astConfigObj->addEntry($entry);
            }
        } else {
            $astConfigObj = false;
        }
        return $astConfigObj;
    }

    public static function retrieveByConfig($config)
    {
        $c = [];

        $providerName = Helper::getClass("iface.provider.pbx.astConfig");
        /** @var AstConfigProvider $provider */
        $provider = $providerName::create();

        $entries = $provider
            ->filterByFilename($config)
            ->filterByCategory('general', Criteria::NOT_EQUAL)
            ->orderByCatMetric()
            ->orderByVarMetric()
            ->find();

        if ($entries->count() > 0) {
            $class = get_called_class();


            /** @var AstConfig $row */
            foreach ($entries as $row) {
                $category = $row->getCategory();

                if (array_key_exists($category, $c)) {
                    $astConfigObj = $c[$category];
                } else {
                    /** @var $astConfigObj ConfigAbstract */
                    $astConfigObj = new self($config, $category);
                    $c[$category] = $astConfigObj;
                }
                $entry = new Entry($row);
                $astConfigObj->addEntry($entry);
            }
        }
        return $c;
    }

}