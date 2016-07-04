<?php

abstract class Dfi_Asterisk_Static_ConfigAbstract
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
     * @param BaseObject $propelObject
     * @return Dfi_Asterisk_Static_ConfigAbstract
     */
    public static function create(BaseObject $propelObject, $addDefaults = false)
    {
        $replicateFields = self::getReplicateFields();
        $intersect = array_intersect($propelObject->getModifiedColumns(), $replicateFields);
        if ($addDefaults) {
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
        /** @var $object Dfi_Asterisk_Static_ConfigAbstract */
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

                $entry = new Dfi_Asterisk_Static_Entry();
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
        return AstConfigQuery::create()
            ->filterByFilename(self::getFileName())
            ->orderByCategory()
            ->select('Category')
            ->distinct()
            ->find();
    }

    /**
     * @param $category string
     * @return Dfi_Asterisk_Static_ConfigAbstract
     */
    public static function retrieveByCategory($category)
    {
        $entries = AstConfigQuery::create()
            ->filterByFilename(self::getFileName())
            ->filterByCategory($category)
            ->orderByVarMetric()
            ->find();

        if ($entries->count() > 0) {
            $class = get_called_class();

            /** @var $obj Dfi_Asterisk_Static_ConfigAbstract */
            $obj = new $class($category);
            foreach ($entries as $row) {
                $entry = new Dfi_Asterisk_Static_Entry($row);
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


    ////////


    public function __construct()
    {

    }

    public function modify(BaseObject $propelObject)
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
                    /** @var Dfi_Asterisk_Static_Entry $entry */
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
            /** @var $entry Dfi_Asterisk_Static_Entry */
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
        $i = 1;
        /** @var $entry Dfi_Asterisk_Static_Entry */
        foreach ($this->entries as $key => $entry) {

            $entry->updateCategory($this->category);
            $entry->updateCatMetric($this->cat_metric);
            $entry->updateVarMetric($i);

            $res = $entry->save($this->getPdo());
            if ($res) {
                $this->isModified = true;
            }
            $i++;
        }
        if ($this->isModified && $reloadAsterisk) {
            if ($this instanceof Dfi_Asterisk_Static_Dialplan) {
                Dfi_Asterisk_Ami::reloadDialplan();
            } else {
                Dfi_Asterisk_Ami::reload();
            }
        }
    }

    public function delete()
    {
        /** @var $entry Dfi_Asterisk_Static_Entry */
        foreach ($this->entries as $entry) {
            $entry->delete();
        }
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
     * @param Dfi_Asterisk_Static_Entry $entry
     */
    public function addEntry(Dfi_Asterisk_Static_Entry $entry)
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
        /** @var Dfi_Asterisk_Static_Entry $entry */
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

            $entry = new Dfi_Asterisk_Static_Entry();
            $entry->var_name = $attrib;
            $entry->var_val = $value;
            $this->addEntry($entry);
        }

    }

    /**
     * @param $variable
     * @return bool|Dfi_Asterisk_Static_Entry
     */
    public function getEntry($variable)
    {

        $key = array_search($variable, $this->keysIndex);
        if (false !== $key) {
            return $this->entries[$key];
        }

        return false;

        $keys = array_filter($this->keysIndex, function ($element) use ($variable) {
            return $element == $variable;
        });
        if (false !== $keys) {
            $z = array_intersect_key($this->entries, $keys);
        }


        return false;

    }

    /**
     * @param $variable
     * @return bool|Dfi_Asterisk_Static_Entry
     */
    public function getEntriesByKeyAndValue($variable, $value)
    {
        $keys = array_filter($this->keysIndex, function ($element) use ($variable) {
            return $element == $variable;
        });

        $res = [];
        $found = array_intersect_key($this->entries, $keys);

        /** @var Dfi_Asterisk_Static_Entry $entry */
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
        /** @var $entry Dfi_Asterisk_Static_Entry */
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
                $entry = new Dfi_Asterisk_Static_Entry();
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
     * @param Dfi_Asterisk_Static_Entry $entry
     * @throws Exception
     */
    private function prepareEntry(Dfi_Asterisk_Static_Entry $entry)
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
     * @param Dfi_Asterisk_Static_Entry $entry
     * @return bool
     */
    private function checkEntry(Dfi_Asterisk_Static_Entry $entry)
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


}