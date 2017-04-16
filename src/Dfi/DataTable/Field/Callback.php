<?
namespace Dfi\DataTable\Field;

use Exception;
use ModelCriteria;

class Callback extends FieldAbstract implements FieldInterface
{

    private $helperClass;
    private $helperMethod;

    /**
     * @var FieldInterface
     */
    private $dataHelper;

    /**
     * @param string $helperClass
     * @param string $helperMethod
     * @param FieldInterface $dataHelper
     * @return Callback
     */
    public static function create($helperClass, $helperMethod, FieldInterface $dataHelper)
    {
        $helper = new Callback();

        $helper->setHelperClass($helperClass);
        $helper->setHelperMethod($helperMethod);
        $helper->setDataHelper($dataHelper);


        return $helper;
    }


    public function getValue($row, &$errors)
    {
        $helper = new $this->helperClass();
        $data = $this->dataHelper->getValue($row, $errors);
        return call_user_func(array($helper, $this->helperMethod), $data, $this->options);
    }

    /**
     * @param FieldInterface $dataHelper
     */
    public function setDataHelper(FieldInterface $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param mixed $helperClass
     */
    public function setHelperClass($helperClass)
    {
        $this->helperClass = $helperClass;
    }

    /**
     * @param mixed $helperMethod
     */
    public function setHelperMethod($helperMethod)
    {
        $this->helperMethod = $helperMethod;
    }

    public function getColumns($query = null)
    {
        return $this->dataHelper->getColumns();
    }


    public function setOrder(ModelCriteria $query, $direction)
    {
        // TODO: Implement setOrder() method.

        throw new Exception('not implemented');
    }


}