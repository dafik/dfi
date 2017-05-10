<?

namespace Dfi\Form\Element;

use Criteria;
use Dfi\Filter\NullFilter;
use Exception;
use ModelCriteria;
use TableMap;
use Zend_View_Interface;

class Lists extends Select
{
    const SET_MODEL_NAME_NOT_SET = 'you must set model name first';

    private $modelName;
    private $modelField;
    private $modelValueField;

    /**
     * @var  ModelCriteria
     */
    private $criteria;
    private $correctName = false;
    private $objectList = array();
    private $listGenerated = false;

    private $selects = array();

    public function __construct($spec, $options = null)
    {
        $this->setDisableOptionsTranslator(true);
        parent::__construct($spec, $options);
        $this->addFilter(new NullFilter());
    }


    public function render(Zend_View_Interface $view = null)
    {
        $this->setDisableTranslator(true);
        if (count($this->getMultiOptions()) == 0) {
            $this->setMultiOptions(array());
        }
        $this->setDisableTranslator(false);
        return parent::render($view);
    }

    public function setValue($value)
    {
        if (count($this->getMultiOptions()) == 0) {
            $this->setMultiOptions(array());
        }
        parent::setValue($value);
    }

    public function isValid($value, $context = null)
    {
        if (count($this->getMultiOptions()) == 0) {
            $this->setMultiOptions(array());
        }
        return parent::isValid($value, $context);
    }

    public function setMultiOptions(array $options)
    {
        $this->setDisableTranslator(true);
        parent::setMultiOptions($this->generateMultiOptions());
        $this->setDisableTranslator(false);
    }

    public function setModelName($modelName)
    {
        if (!$modelName) {
            throw new Exception('model cant be empty');
        }
        try {
            $model = new $modelName;
        } catch (Exception $e) {
            throw new Exception('model ' . $modelName . ' doesn\'t exist');
        }
        $this->correctName = true;
        $this->modelName = $modelName;
    }

    public function setModelField($modelField)
    {
        if (!$modelField) {
            throw new Exception('model filed cant be empty');
        }

        if (!is_array($modelField)) {
            $modelField = array($modelField);
        }

        foreach ($modelField as $field) {

            $functionName = 'get' . ucfirst($field);
            if (null == $this->modelName) {
                throw new Exception(self::SET_MODEL_NAME_NOT_SET . ' in' . $this->getName());
            }
            $name = $this->modelName;
            $model = new $name;

            if (method_exists($model, $functionName)) {
                $this->modelField[] = $field;
            } else {
                throw new Exception($functionName . ' function doesn\'t exists');
            }
        }
        return true;

    }

    public function setModelValueField($nameValue)
    {
        if (!$nameValue) {
            throw new Exception('model value cant be empty');
        }

        if (null == $this->modelName) {
            throw new Exception(self::SET_MODEL_NAME_NOT_SET . ' in' . $this->getName());
        }
        $name = $this->modelName;
        $peerName = $name . 'Peer';

        $cons = constant($peerName . '::' . strtoupper($nameValue));
        if ($cons === null) {
            throw new Exception('can\'t found model value field: ' . $nameValue);
        }
        $this->modelValueField = strtoupper($nameValue);
    }

    public function setCriteria(Criteria $c)
    {
        $this->criteria = $c;
    }

    private function generateMultiOptions()
    {
        if (!$this->correctName) {
            throw new Exception('method require valid model name to be set first in: ' . $this->getName());
        }
        $data = $this->getDataList();
        $options = $this->generateOptions($data);
        return $options;
    }

    private function getDataList()
    {
        if (!$this->listGenerated) {
            if ($this->criteria instanceof Criteria) {
                $name = $this->modelName . 'Query';
                /** @var ModelCriteria $c */
                $c = $name::create();
                $c->mergeWith($this->criteria);
            } else {
                $name = $this->modelName . 'Query';
                $c = $name::create();
            }
            if (!$c instanceof ModelCriteria) {
                throw new Exception('old method');
            }
            $this->criteria = $c;

            $this->configureSelect();


            $objects = $c->find()->toArray();;
            $this->objectList = $objects;
            $this->listGenerated = true;
        }
        return $this->objectList;
    }

    private function generateOptions(array $objectList)
    {
        $list = array();
        $list['null'] = 'wybierz';

        $pk = $this->modelValueField;
        $select = $this->selects;
        unset($select[array_search($pk, $select)]);

        if (count($objectList) > 0) {
            foreach ($objectList as $object) {
                $out = [];
                foreach ($select as $arg) {
                    $out[] = $object[$arg];
                }

                $list[$object[$pk]] = implode(' ', $out);
            }
        }
        return $list;
    }


    private function configureSelect()
    {
        $selects = [];

        if (!$this->modelValueField) {
            $pks = $this->getTableMap()->getPrimaryKeyColumns();
            if (count($pks) > 1) {
                throw new Exception('auto guess model value field failed : to many pks');
            }

            $pkColumn = $pks[0];
            $this->modelValueField = $pkColumn->getPhpName();
        }
        $selects[] = $this->modelValueField;

        if (!$this->modelField) {
            $map = $this->getTableMap();
            foreach ($map->getColumns() as $column) {
                if (false !== strpos('name', strtolower($column->getPhpName()))) {
                    $this->modelField = $column->getPhpName();
                    break;
                }
            }
        }
        if (!$this->modelField) {
            throw new Exception('auto guess model field failed : name column not found');
        }
        if (is_string($this->modelField)) {
            $this->modelField = [$this->modelField];
        }

        $selects = array_unique(array_merge($selects, $this->modelField));
        $this->selects = $selects;

        $this->criteria->select($selects);
    }

    /**
     * @return TableMap
     */
    private function getTableMap()
    {
        $name = $this->modelName . 'Query';
        /** @var ModelCriteria $c */
        $c = $name::create();
        return $c->getTableMap();
    }

    public function getMultiOptions()
    {
        return parent::getMultiOptions(); // TODO: Change the autogenerated stub
    }

}