<?php

namespace Dfi\Asterisk\Stat;


use Dfi\Asterisk\Ami;
use Dfi\Iface\Helper;
use Dfi\Iface\Model;
use Dfi\Iface\Provider\Pbx\AstConfigProvider;
use Exception;
use PDO;
use Propel;
use TableMap;
use Zend_Config_Ini;

abstract class ConfigAbstract
{


    /**
     * @var int
     */
    protected $cat_metric;
    /**
     * @var int
     */
    protected $var_metric;
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var string
     */
    protected $category;
    /**
     * @var bool
     */
    protected $commented = 0;

    protected static $attributes = [];
    protected static $attributeValues = [];
    protected static $categoryField;
    protected static $transTable = [];
    protected static $transTableGetters = [];

    protected $keysIndex = [];
    protected $entries = [];

    /**
     * @var bool
     */
    protected $isModified = false;


    /**
     * @param Model $propelObject
     * @return ConfigAbstract
     */
    public static function create(Model $propelObject, $addDefaults = false, $doIntersect = false)
    {
        $replicateFields = self::getReplicateFields();
        if ($doIntersect) {
            $intersect = array_intersect($propelObject->getModifiedColumns(), $replicateFields);
        } else {
            $intersect = $replicateFields;
        }
        $categoryField = self::getCategoryField();
        $transTable = static::$transTable;
        $class = get_class($propelObject);
        $peer = constant($class . '::PEER');
        /** @var $tableMap TableMap */
        $tableMap = $peer::getTableMap();

        $method = 'get' . $tableMap->getColumn($categoryField)->getPhpName();
        $category = $propelObject->$method();

        $className = get_called_class();
        /** @var $object ConfigAbstract */
        $object = new $className($category);

        if ($addDefaults || $propelObject->isNew()) {
            $object->applyDefaults();
        }


        $state = true;
        if (method_exists($propelObject, 'getIsActive')) {
            $state = $propelObject->getIsActive();
        }


        foreach ($intersect as $field) {
            if ($field != $categoryField) {
                $name = $transTable[$field];

                if (isset(static::$transTableGetters[$field])) {
                    $method = static::$transTableGetters[$field];
                } else {
                    $method = 'get' . $tableMap->getColumn($field)->getPhpName();
                }

                $value = $propelObject->$method();

                $entry = new Entry();
                $entry->updateName($name);
                $entry->updateValue($value);
                $object->addEntry($entry);
                $entry->updateCommented(!$state);

            }
        }
        return $object;
    }

    public static function getCategories()
    {
        $providerName = Helper::getClass("iface.provider.pbx.astConfig");
        /** @var AstConfigProvider $provider */
        $provider = $providerName::create();

        return $provider
            ->filterByFilename(self::getFileName())
            ->orderByCategory()
            ->select('Category')
            ->distinct()
            ->find();
    }

    /**
     * @param $category string
     * @return ConfigAbstract
     */
    public static function retrieveByCategory($category)
    {
        $providerName = Helper::getClass("iface.provider.pbx.astConfig");
        /** @var AstConfigProvider $provider */
        $provider = $providerName::create();

        $entries = $provider
            ->filterByFilename(self::getFileName())
            ->filterByCategory($category)
            ->orderByVarMetric()
            ->find();

        if ($entries->count() > 0) {
            $class = get_called_class();

            /** @var $obj ConfigAbstract */
            $obj = new $class($category);
            foreach ($entries as $row) {
                $entry = new Entry($row);
                $obj->addEntry($entry);
            }
        } else {
            $obj = false;
        }
        return $obj;
    }

    public static function getAttributes()
    {
        return array_keys(static::$attributeValues);
    }

    public static function getReplicateFields()
    {
        $fields = array_keys(static::$transTable);
        return $fields;
    }

    public static function prepareAttributes()
    {
        if (count(self::$attributes) == 0) {
            $class = static::class;
            $file = constant($class . '::FILE_NAME');
            $file = str_replace('.conf', '', $file);

            $config = new Zend_Config_Ini('configs/ini/configs.ini', APPLICATION_ENV);


            self::$attributes = array_keys($config->toArray()[$file]['entry']);
            sort(self::$attributes);
        }
    }

    public static function getCategoryField()
    {
        return static::$categoryField;
    }


    protected static function getFileName()
    {
        $class = get_called_class();
        if (defined($class . '::FILE_NAME')) {
            return constant($class . '::FILE_NAME');
        }
        throw new Exception('FILE_NAME not defined in ' . $class);
    }

    protected static function getConfig()
    {
        $config = new Zend_Config_Ini('configs/ini/asterisk.ini', APPLICATION_ENV);
        return $config->toArray();
    }

    /**
     * @return PDO
     */
    private static function getPdo()
    {

        return Propel::getConnection();
    }


    public function modify(Model $propelObject, $defaults)
    {
        $replicateFields = self::getReplicateFields();
        $intersect = array_intersect($propelObject->getModifiedColumns(), $replicateFields);
        if (count($intersect) > 0) {
            $categoryField = self::getCategoryField();
            $transTable = static::$transTable;
            $class = get_class($propelObject);
            $peer = constant($class . '::PEER');
            /** @var $tableMap TableMap */
            $tableMap = $peer::getTableMap();


            foreach ($intersect as $field) {
                $this->isModified = true;
                if (isset(static::$transTableGetters[$field])) {
                    $method = static::$transTableGetters[$field];
                } else {
                    $method = 'get' . $tableMap->getColumn($field)->getPhpName();
                }
                $value = $propelObject->$method();

                if ($field != $categoryField) {
                    $entries = $this->getEntriesByKeyAndValue($transTable[$field], $propelObject->getOldValue($field));
                    /** @var Entry $entry */
                    foreach ($entries as $entry) {
                        $entry->updateValue($value);
                    }

                } else {
                    $this->setCategory($field);
                }
            }
        }
        if (method_exists($propelObject, 'getIsActive')) {
            $state = $propelObject->getIsActive();
            /** @var $entry Entry */
            foreach ($this->entries as $entry) {
                $entry->updateCommented(!$state);
            }
        }
        if (method_exists($propelObject, 'getOldName') && $propelObject->getOldName()) {
            $class = get_class($propelObject);
            $peer = constant($class . '::PEER');
            /** @var $tableMap TableMap */
            $tableMap = $peer::getTableMap();

            $method = 'get' . $tableMap->getColumn(static::$categoryField)->getPhpName();
            $this->category = $propelObject->$method();
        }

        ///definitions
        $this->removeDefinitions();

        if (method_exists($propelObject, 'getDefinition')) {
            $this->applyDefinitions($propelObject->getDefinition());
        }


    }

    public function save($reloadAsterisk = true)
    {

        if (!$this instanceof Dialplan) {
            $this->removeDuplicates();
        }
        $i = 1;
        /** @var $entry Entry */
        foreach ($this->entries as $key => $entry) {

            $entry->updateCategory($this->category);
            $entry->updateCatMetric($this->cat_metric);
            if (!$entry->isIsDeleted()) {
                $entry->updateVarMetric($i);
                $i++;
            }
            $res = $entry->save($this->getPdo());
            if ($res) {
                $this->isModified = true;
            }

        }
        if ($this->isModified && $reloadAsterisk) {
            if ($this instanceof Dialplan) {
                Ami::reloadDialplan();
            } else {
                Ami::reload();
            }
        }
    }

    public function delete()
    {
        /** @var $entry Entry */
        foreach ($this->entries as $entry) {
            $entry->delete();
        }
        return $this;
    }


    public function getEntriesArray()
    {
        return $this->entries;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }


    /**
     * @param Entry $entry
     */
    public function addEntry(Entry $entry)
    {
        $check = $this->checkEntry($entry);
        if (!$check) {
            $this->prepareEntry($entry);
        } else {
            $this->cat_metric = $entry->cat_metric;
        }

        $key = array_push($this->entries, $entry);
        $this->keysIndex[$key - 1] = $entry->var_name;

    }


    public function getDefinitions()
    {
        $ret = [];
        /** @var Entry $entry */
        foreach ($this->entries as $entry) {
            $name = $entry->var_name;

            if (!$entry->isIsDeleted() && !in_array($name, static::$transTable)) {
                $ret[] = $name . '=' . $entry->var_val;
            }
        }
        return $ret;
    }


    public function applyDefaults()
    {

        foreach (static::$attributeValues as $attrib => $value) {

            $entry = new Entry();
            $entry->var_name = $attrib;
            $entry->var_val = $value;
            $this->addEntry($entry);
        }

    }

    /**
     * @param $variable
     * @return bool|Entry
     */
    public function getEntry($variable)
    {

        $key = array_search($variable, $this->keysIndex);
        if (false !== $key) {
            return $this->entries[$key];
        }

        return false;

        /*        $keys = array_filter($this->keysIndex, function ($element) use ($variable) {
                    return $element == $variable;
                });
                if (false !== $keys) {
                    $z = array_intersect_key($this->entries, $keys);
                }


                return false;*/

    }

    /**
     * @param $variable
     * @return bool|Entry
     */
    public function getEntriesByKeyAndValue($variable, $value)
    {
        $keys = array_filter($this->keysIndex, function ($element) use ($variable) {
            return $element == $variable;
        });

        $res = [];
        $found = array_intersect_key($this->entries, $keys);

        /** @var Entry $entry */
        foreach ($found as $entry) {
            if ($entry->var_val == $value) {
                $res[] = $entry;
            }
        }


        return $res;

    }


    protected function countEntries()
    {
        return count($this->entries);
    }


    /**
     * @param string $category
     */
    protected function setCategory($category)
    {
        $this->category = $category;
        /** @var $entry Entry */
        foreach ($this->entries as $entry) {
            $entry->updateCategory($category);
        }
    }


    protected function applyDefinitions($definitionDef)
    {

        $def = explode("\n", $definitionDef);
        foreach ($def as $definition) {
            if (false !== strpos($definition, '=')) {
                list($name, $val) = explode('=', $definition);
                $entry = new Entry();
                $entry->var_name = trim($name);
                $entry->var_val = trim($val);
                $this->addEntry($entry);
            }
        }
    }

    private function removeDefinitions()
    {

        foreach ($this->keysIndex as $key => $value) {
            if (false === array_search($value, static::$transTable)) {
                $this->entries[$key]->delete();
            }
        }

    }

    /**
     * @param Entry $entry
     * @throws Exception
     */
    private function prepareEntry(Entry $entry)
    {
        if ($this->cat_metric) {
            $entry->cat_metric = $this->cat_metric;
        } else {


            $sqlMax = "SELECT IFNULL(max(a.`cat_metric`),0) + 1 FROM ast_config a WHERE a.`filename` = '" . $this->filename . "' ";
            $stmt = $this->getPdo()->query($sqlMax);

            $maxCatMetric = $stmt->fetchColumn(0);
            $this->cat_metric = $entry->cat_metric = $maxCatMetric;
        }
        if ($this->var_metric || $this->var_metric === 1) {

            $entry->var_metric = $this->var_metric + 1;
        } else {
            $entry->var_metric = 1;
        }
        $this->var_metric = $entry->var_metric;
        $entry->commented = $this->commented;
        $entry->filename = $this->filename;
        $entry->category = $this->category;

        /*if (!in_array($entry->var_name, $this->getAttributes())) {
            throw new Exception('unknown attribute: ' . $entry->var_name);
        }*/
        if (null === $entry->var_val) {
            throw new Exception('value can\'t be null ' . $entry->var_name);
        }
    }

    /**
     * @param Entry $entry
     * @return bool
     */
    private function checkEntry(Entry $entry)
    {
        $requiredProperties = array('cat_metric', 'var_metric', 'commented', 'filename', 'category', 'var_name', 'var_val');
        foreach ($requiredProperties as $property) {
            if (null === $entry->$property) {
                return false;
            }
        }
        return true;
    }


    //not used

    protected function setFileName($filename)
    {
        $this->filename = $filename;
    }

    private function removeDuplicates()
    {
        $tmp = [];
        foreach ($this->keysIndex as $key => $name) {
            if (!isset($tmp[$name])) {
                $tmp[$name] = [];
            }
            $tmp[$name][] = $key;
        }

        $tmp2 = [];

        foreach ($tmp as $name => $keys) {
            if (count($keys) > 1) {
                foreach ($keys as $key) {
                    /** @var Entry $entry */
                    $entry = $this->entries[$key];
                    $tmp2[$key] = $entry->var_name . $entry->var_val;
                }
                $tmp2 = array_keys(array_unique($tmp2));
                foreach ($keys as $key) {
                    if (false === array_search($key, $tmp2)) {
                        $this->entries[$key]->delete();
                    }
                }
            }
        }
    }
}