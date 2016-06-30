<?php

class Dfi_Asterisk_Static_Entry
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $cat_metric;
    /**
     * @var int
     */
    public $var_metric;
    /**
     * @var bool
     */
    public $commented;
    /**
     * @var string
     */
    public $filename;
    /**
     * @var  string
     */
    public $category;
    /**
     * @var string
     */
    public $var_name;
    /**
     * @var string
     */
    public $var_val;

    /**
     * @var bool
     */
    private $isModified = false;
    /**
     * @var bool
     */
    private $isNew = false;
    /**
     * @var bool
     */
    private $isDeleted = false;

    /**
     * @param null|array $row
     */
    public function __construct($row = null)
    {
        if (is_object($row)) {
            $values = $row->toArray();
            $tmp = [];
            foreach ($values as $key => $value) {
                $tmp[$row->getPeer()->getTableMap()->getColumnByPhpName($key)->getName()] = $value;
            }
            $row = $tmp;

        }

        if ($row) {
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
        } else {
            $this->isNew = true;
        }
    }

    /**
     * @param PDO $pdo
     * @return bool
     */
    public function save(PDO $pdo)
    {
        $this->filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->filename);
        $this->category = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->category);
        $this->var_name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->var_name);
        $this->var_val = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->var_val);


        if ($this->isDeleted) {
            $sql = 'DELETE FROM `ast_config` WHERE `id` = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $this->id);
            $stmt->execute();
            return true;
        }

        if ($this->isNew) {
            $sql = 'INSERT INTO `ast_config` ( `cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val` )	VALUE ( :cat_metric, :var_metric, :commented, :filename, :category, :var_name, :var_val )';
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':cat_metric', $this->cat_metric);
            $stmt->bindValue(':var_metric', $this->var_metric);
            $stmt->bindValue(':commented', $this->commented);
            $stmt->bindValue(':filename', $this->filename);
            $stmt->bindValue(':category', $this->category);
            $stmt->bindValue(':var_name', $this->var_name);
            $stmt->bindValue(':var_val', $this->var_val);
            $stmt->execute();
            return true;
        }

        if ($this->isModified) {
            $sql = 'UPDATE `ast_config` SET `cat_metric` = :cat_metric, `var_metric` = :var_metric, `commented` = :commented, `filename` = :filename, 	`category` = :category, `var_name` = :var_name, `var_val` = :var_val WHERE `id` = :id';
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':cat_metric', $this->cat_metric);
            $stmt->bindValue(':var_metric', $this->var_metric);
            $stmt->bindValue(':commented', $this->commented);
            $stmt->bindValue(':filename', $this->filename);
            $stmt->bindValue(':category', $this->category);
            $stmt->bindValue(':var_name', $this->var_name);
            $stmt->bindValue(':var_val', $this->var_val);
            $stmt->bindValue(':id', $this->id);
            $stmt->execute();

            return true;
        }


        return false;
    }

    /**
     *
     */
    public function delete()
    {
        $this->isDeleted = true;
    }

    public function updateName($value)
    {
        if ($this->var_name = $value) {
            $this->var_name = $value;
            $this->isModified = true;
        }
    }

    public function updateValue($value)
    {
        if ($this->var_val != $value) {
            $this->var_val = $value;
            $this->isModified = true;
        }
    }

    public function updateCategory($value)
    {
        if ($this->category != $value) {
            $this->category = $value;
            $this->isModified = true;
        }
    }

    public function updateCatMetric($value)
    {
        if ($this->cat_metric != $value) {
            $this->cat_metric = $value;
            $this->isModified = true;
        }
    }

    public function updateVarMetric($value)
    {
        if ($this->var_metric != $value) {
            $this->var_metric = $value;
            $this->isModified = true;
        }
    }

    public function updateCommented($value)
    {
        $value = (bool)$value;
        if ($this->commented != $value) {
            $this->commented = $value;
            $this->isModified = true;
        }
    }
}