<?php
namespace Dfi\DataTable\Field;

use ModelCriteria;

class Custom extends FieldAbstract implements FieldInterface
{

    public function __construct($key)
    {
        $this->key = $key;
    }

    public static function create($key)
    {
        return new Key($key);
    }

    public function getValue($row, &$errors, $key = false)
    {
        $value = false;
        if (!$key) {
            $key = $this->key;
        }

        if (!isset($row[$key]) && (false !== strpos($key, '.'))) {
            list($subRowName, $key) = explode('.', $key);
            if (isset($row[$subRowName])) {
                $value = $this->getValue($row[$subRowName], $errors, $key);
            } else {
                if ($this->hasOption('notFoundWarn')) {
                    if ($this->getOption('notFoundWarn')) {
                        $errors[] = 'cant found ' . $subRowName . ' in data';
                    }
                } else {
                    $errors[] = 'cant found ' . $subRowName . ' in data';
                }

            }
        } else {
            if (array_key_exists($key, $row)) {
                $value = $row[$key];
            } else {
                if ($this->hasOption('notFoundWarn')) {
                    if ($this->getOption('notFoundWarn')) {
                        $errors[] = 'cant found ' . $key . ' in data';
                    }
                } else {
                    $errors[] = 'cant found ' . $key . ' in data';
                }
            }
        }

        return $value;
    }

    public function getColumns($query = null)
    {
        return [$this->key];
    }


    public function setOrder(ModelCriteria $query, $direction)
    {
        $query->orderBy($this->key, $direction);
    }

    public function applyFilter($query, $value, $operator)
    {
        if ($this->getOption('filter') == 'date-range') {

            list($min, $max) = explode('do', $value);
            $min = trim($min);
            if (strlen($min) == '10') {
                $min .= ' 00:00:00';
            }
            $max = trim($max);
            if (strlen($max) == '10') {
                $max .= ' 23:59:59';
            }
            $value = array(
                'min' => $min,
                'max' => $max
            );
        }


        parent::applyFilter($query, $value, $operator);
    }


}