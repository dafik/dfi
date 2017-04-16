<?
namespace Dfi\Form\Element;
use Criteria;
use Exception;
use Zend_Form_Element_Multiselect;
use Zend_View_Interface;

class Multilist extends Zend_Form_Element_Multiselect
{
const SET_MODEL_NAME_NOT_SET ='you must set model name first';

  private $modelName;
  private $modelField;
  private $modelValueField;
  private $criteria;
  private $orderByFields = array();
  private $correctName = false;
  private $objectList = array();
  private $listGenerated = false;

  public function render(Zend_View_Interface $view = null)
  {
    if (count($this->getMultiOptions())==0) {
      $this->setMultiOptions();
    }

    return  parent::render($view);
  }
  public function setValue($value){
    if (count($this->getMultiOptions())==0) {
      $this->setMultiOptions();
    }
    parent::setValue($value);
  }
  public function isValid($value, $context = null)
  {
    if (count($this->getMultiOptions())==0) {
      $this->setMultiOptions();
    }
    return parent::isValid($value, $context);
  }
  public function setMultiOptions()
  {
    parent::setMultiOptions($this->generateMultiOptions());
  }
  public function setModelName($modelName)
  {
    try{
      $name = ucfirst($modelName);
      new $name;
    }catch (Exception $e){
      throw new Exception('model '.$modelName.' doesn\'t exist');
    }
    $this->correctName = true;
    $this->modelName = $modelName;
  }
  public function setModelField($modelField)
  {
    $functionName = 'get'.ucfirst($modelField);
    if (null == $this->modelName)   {
      throw new Exception(self::SET_MODEL_NAME_NOT_SET);
    }
    $name = ucfirst($this->modelName);
    $model = new $name;

    if (method_exists($model,$functionName))    {
      $this->modelField = $modelField;
      return true;
    }
    throw new Exception($functionName.' function doesn\'t exists');
  }

  public function setModelValueField($nameValue)
  {
    if (null == $this->modelName) {
      throw new Exception(self::SET_MODEL_NAME_NOT_SET);
    }
    $name = ucfirst($this->modelName);
    $peerName = $name.'Peer';

    $cons = constant($peerName.'::'.strtoupper($nameValue));
    if ($cons === null){
      throw new Exception('can\'t found model value field: '.$nameValue);
    }
    $this->modelValueField = strtoupper($nameValue);
  }

  public function setCriteria(Criteria $c)
  {
    $this->criteria = $c;
  }

  public function setOrderByFields(array $orderBy){
    $this->orderByFields = $orderBy;
  }
  private function generateMultiOptions()
  {
    if (!$this->correctName) {
      throw new Exception('method require valid model name to be set first');
    }
    //$objectList =$this->getObjectList();
    $options = $this->propelObject2array($this->getObjectList(),$this->modelValueField,$this->modelField);
    //$options = $this->propelObject2array($this->getObjectList());
    return $options;
  }
  private function getObjectList(){
    if (!$this->listGenerated) {
      if ($this->criteria instanceof Criteria ) {
        $c = $this->criteria;
      } else {
        $c = new Criteria();
      }
      $peerClass = ucfirst($this->modelName).'Peer';

      foreach ($this->orderByFields as $orderByField => $direction) {
        $const = constant($peerClass.'::'.strtoupper($orderByField));
        if (null != $const) {
          if (strtoupper($direction) == 'ASC')  {
            $c->addAscendingOrderByColumn($const);
          }
          if (strtoupper($direction) == 'DESC')  {
            $c->addDescendingOrderByColumn($const);
          }
        } else {
          throw new Exception('couldn\'t find field: '.$orderByField.' in '.$peerClass.' class');
        }
      }



      $objects = call_user_func(array($peerClass,'doSelect'),$c);
      $this->objectList = $objects;
      $this->listGenerated = true;
    }
    return $this->objectList;
  }
  public function propelObject2array(array $objectList,$pk = null,$displayField = null)
  {
    $list = array();
    //$list['x'] = 'wybierz';

    if (count($objectList) >0) {
      $object = $objectList[0];
      if (null === $pk) {

        $pks = $object->getPeer()->getTableMap()->getPrimaryKeyColumns();

        $pkColumn = $pks[0];
        $pk = $pkColumn->getPhpName();
      }
      $pkMethod = 'get'.ucfirst($pk);
      if (null === $displayField) {
        if (method_exists($object,'getName')){
          $displayField = 'name';
        } elseif ( null != $this->modelField ) {
          $displayField = $this->modelField;
        } else {
          throw new Exception('can\'t found displayfield');
        }
      }
      $displayFieldMethod =  'get'.ucfirst($displayField);
      foreach ($objectList as $object) {
        $list[$object->$pkMethod()] = $object->$displayFieldMethod();
      }
    }
    return $list;
  }
	}