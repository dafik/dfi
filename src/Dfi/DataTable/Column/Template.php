<?
namespace Dfi\DataTable\Column;

use Exception;
use Zend_Controller_Front;

class Template
{
    private $template;
    private $path = false;
    private $name;

    public function __construct($name, $template)
    {
        $this->template = $template;
        $this->name = $name;
    }

    public static function create($name, $template)
    {
        return new Template($name, $template);
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function render()
    {
        if (!$this->path) {
            $front = Zend_Controller_Front::getInstance();
            $request = $front->getRequest();
            $dir = $front->getModuleDirectory($request->getModuleName());
            $path = $dir . '/dataTables/' . $request->getControllerName() . '/' . $request->getActionName();
        } else {
            $path = $this->path;
        }
        $file = $path . '/' . $this->template;
        if (!file_exists($file)) {
            throw new Exception('template ' . $this->template . ' not found');
        }

        $template = file_get_contents($file);


        return str_replace("\n", ' ', $template);


    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

}