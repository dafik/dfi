<?php

class Dfi_Asterisk_Static_Generic extends Dfi_Asterisk_Static_ConfigAbstract
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
     * @return Dfi_Asterisk_Static_ConfigAbstract
     */
    public static function retrieveByConfigAndCategory($config, $category)
    {

        $entries = AstConfigQuery::create()
            ->filterByFilename($config)
            ->filterByCategory($category)
            ->orderByVarMetric()
            ->find();

        if ($entries->count() > 0) {
            $class = get_called_class();

            /** @var $astConfigObj Dfi_Asterisk_Static_ConfigAbstract */
            $astConfigObj = new self($config, $category);
            foreach ($entries as $row) {
                $entry = new Dfi_Asterisk_Static_Entry($row);
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

        $entries = AstConfigQuery::create()
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
                    /** @var $astConfigObj Dfi_Asterisk_Static_ConfigAbstract */
                    $astConfigObj = new self($config, $category);
                    $c[$category] = $astConfigObj;
                }
                $entry = new Dfi_Asterisk_Static_Entry($row);
                $astConfigObj->addEntry($entry);
            }
        }
        return $c;
    }

}