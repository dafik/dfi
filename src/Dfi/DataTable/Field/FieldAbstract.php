<?

namespace Dfi\DataTable\Field;

use Criteria;
use Dfi\DataTable\Filter;
use Exception;
use ModelCriteria;


abstract class FieldAbstract implements FieldInterface
{

    protected $options = [];
    protected $key;


    protected $ops = [
        /** Comparison type. */
        Criteria::EQUAL => "=",

        /** Comparison type. */
        Criteria::NOT_EQUAL => "<>",

        /** Comparison type. */
        Criteria::ALT_NOT_EQUAL => "!=",

        /** Comparison type. */
        Criteria::GREATER_THAN => ">",

        /** Comparison type. */
        Criteria::LESS_THAN => "<",

        /** Comparison type. */
        Criteria::GREATER_EQUAL => ">=",

        /** Comparison type. */
        Criteria::LESS_EQUAL => "<=",

        /** Comparison type. */
        Criteria::LIKE => " LIKE ",

        /** Comparison type. */
        Criteria::NOT_LIKE => " NOT LIKE ",

        /** Comparison for array column types */
        Criteria::CONTAINS_ALL => "CONTAINS_ALL",

        /** Comparison for array column types */
        Criteria::CONTAINS_SOME => "CONTAINS_SOME",

        /** Comparison for array column types */
        Criteria::CONTAINS_NONE => "CONTAINS_NONE",

        /** PostgreSQL comparison type */
        Criteria::ILIKE => " ILIKE ",

        /** PostgreSQL comparison type */
        Criteria::NOT_ILIKE => " NOT ILIKE ",

        /** Comparison type. */
        Criteria::CUSTOM => "CUSTOM",

        /** Comparison type */
        Criteria::RAW => "RAW",

        /** Comparison type for update */
        Criteria::CUSTOM_EQUAL => "CUSTOM_EQUAL",

        /** Comparison type. */
        Criteria::DISTINCT => "DISTINCT",

        /** Comparison type. */
        Criteria::IN => " IN ",

        /** Comparison type. */
        Criteria::NOT_IN => " NOT IN ",

        /** Comparison type. */
        Criteria::ALL => "ALL",

        /** Comparison type. */
        Criteria::JOIN => "JOIN",

        /** Binary math operator: AND */
        Criteria::BINARY_AND => "&",

        /** Binary math operator: OR */
        Criteria::BINARY_OR => "\|",

        /** "Order by" qualifier - ascending */
        Criteria::ASC => "ASC",

        /** "Order by" qualifier - descending */
        Criteria::DESC => "DESC",

        /** "IS NULL" null comparison */
        Criteria::ISNULL => " IS NULL ",

        /** "IS NOT NULL" null comparison */
        Criteria::ISNOTNULL => " IS NOT NULL ",

        /** "CURRENT_DATE" ANSI SQL function */
        Criteria::CURRENT_DATE => "CURRENT_DATE",

        /** "CURRENT_TIME" ANSI SQL function */
        Criteria::CURRENT_TIME => "CURRENT_TIME",

        /** "CURRENT_TIMESTAMP" ANSI SQL function */
        Criteria::CURRENT_TIMESTAMP => "CURRENT_TIMESTAMP",

        /** "LEFT JOIN" SQL statement */
        Criteria::LEFT_JOIN => "LEFT JOIN",

        /** "RIGHT JOIN" SQL statement */
        Criteria::RIGHT_JOIN => "RIGHT JOIN",

        /** "INNER JOIN" SQL statement */
        Criteria::INNER_JOIN => "INNER JOIN",

        /** logical OR operator */
        Criteria::LOGICAL_OR => "OR",

        /** logical AND operator */
        Criteria::LOGICAL_AND => "AND"
    ];


    /**
     * @var Filter
     */
    protected $filter;

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param mixed $filter
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($key)
    {
        return $this->options[$key];
    }

    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }

    public function hasFilter()
    {
        if (count($this->options) > 0) {
            if (isset($this->options['filter'])) {
                return true;
            }
        }
        if ($this->filter) {
            return true;
        }
        return false;
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }


    /**
     * @param ModelCriteria $query
     * @param $value
     * @param $operator
     * @param bool $key
     * @throws Exception
     */
    public function applyFilter(ModelCriteria $query, $value, $operator, $key = false)
    {
        if ($this->filter) {
            $this->applyFilterObject($query, $value, $operator, $key = false);
        }

        if (!$key) {
            $key = $this->key;
        }

        if ($this->hasOption('filterField')) {
            $key = $this->getOption('filterField');
            $operator = Criteria::EQUAL;
        }

        if ($this->hasOption('filter') && $this->getOption('filter') == 'number') {
            $ops = array_values($this->ops);
            $v = implode("|", $ops);

            if (preg_match_all('/' . $v . '/', $value, $matches)) {
                foreach ($matches[0] as $match) {
                    $value = str_replace($match, '', $value);
                    if ($match == '|') {
                        $match = "\|";
                    }
                    $operator = array_search($match, $this->ops);


                }

            } else {
                $operator = Criteria::EQUAL;
            }
        }


        $endUse = 0;

        $method = 'filterBy' . $key;
        if (false !== strpos($key, '.')) {
            list($model, $newKey) = explode('.', $key);
            if (count($this->getOption('path')) == 1) {
                if ($model != $query->getModelName()) {
                    $subQueryMethod = 'use' . $model . 'Query';
                    if (method_exists($query, $subQueryMethod)) {
                        $query = $query->$subQueryMethod();
                        $endUse += 1;
                    } else {
                        throw new Exception('not implemented yet');
                    }
                    $method = 'filterBy' . $newKey;
                } else {
                    $method = 'filterBy' . $newKey;
                }
            } elseif (count($this->getOption('path')) == 0) {
                $method = 'filterBy' . $newKey;
            } else {
                $parts = $this->getOption('path');
                while (count($parts) > 0) {
                    $part = array_shift($parts);

                    $subQueryMethod = 'use' . $part . 'Query';
                    if (method_exists($query, $subQueryMethod)) {
                        $query = $query->$subQueryMethod();
                        $endUse += 1;
                    } else {
                        throw new Exception('not implemented yet');
                    }
                    $method = 'filterBy' . $newKey;

                }
            }
        }
        if (method_exists($query, $method)) {
            $query->$method($value, $operator);
        } else {
            throw new Exception('not implemented yet');
        }

        while ($endUse > 0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $query = $query->endUse();
            $endUse--;
        }

    }

    public function applyFilterObject($query, $value, $operator, $key = false)
    {
        $filter = $this->filter;
        if (!$key) {
            $key = $this->key;
        }

        if ($filter->hasFilterField()) {
            $key = $filter->getFilterField();
            $operator = Criteria::EQUAL;
        }

        if ($filter->getType() == 'number') {
            $operator = Criteria::EQUAL;
        }


        $endUse = 0;

        $method = 'filterBy' . $key;
        if (false !== strpos($key, '.')) {
            list($model, $newKey) = explode('.', $key);

            $subQueryMethod = 'use' . $model . 'Query';

            //$z = FormDatumQuery::create()->useFormRelatedByFormDataIdQuery()

            if (method_exists($query, $subQueryMethod)) {
                $query = $query->$subQueryMethod();
                $endUse += 1;
            } else {
                throw new Exception('not implemented yet');
            }
            $method = 'filterBy' . $newKey;
        }
        if (method_exists($query, $method)) {
            $query->$method($value, $operator);
        } else {
            throw new Exception('not implemented yet');
        }

        if ($endUse > 0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $query->endUse();
        }

    }

    public function getAsColumns(ModelCriteria $query = null)
    {
        return [];
    }

    public function getColumns(ModelCriteria $query = null)
    {
        return [];
    }


}