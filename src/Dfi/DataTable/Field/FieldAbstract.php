<?

namespace Dfi\DataTable\Field;

use Criteria;
use Dfi\Iface\Provider;
use Exception;
use Dfi\DataTable\Filter;
use ModelCriteria;


abstract class FieldAbstract implements FieldInterface
{

    protected $options = [];
    protected $key;


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
            $operator = Criteria::EQUAL;
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