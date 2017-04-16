<?

namespace Dfi\DataTable;

use Dfi\App\Config;
use Dfi\DataTable\Request\Column;
use Dfi\DataTable\Request\Order;
use Zend_Controller_Request_Http;

class Request
{
    private $draw;
    private $start;
    private $length;

    private $search = array('value' => '', 'regex' => '');
    private $hasSearch = false;

    private $order = array();

    private $columns = array();

    private $request;


    public function __construct(Zend_Controller_Request_Http $request)
    {
        $this->request = $request;
        if ($request->isPost()) {
            $this->processRequest();
        }
    }

    private function processRequest()
    {

        $this->draw = $this->request->getParam('draw');
        $this->start = $this->request->getParam('start');
        $this->length = $this->request->getParam('length');
        $this->search = $this->request->getParam('search');
        foreach ($this->request->getParam('order') as $order) {
            $this->order[] = new Order($order['column'], $order['dir']);
        }
        foreach ($this->request->getParam('columns') as $column) {
            $requestColumn = new Column(
                $column['data'],
                $column['name'],
                $column['searchable'],
                $column['orderable'],
                $column['search']
            );
            if ($requestColumn->hasSearch()) {
                $this->hasSearch = true;
            }
            $this->columns[] = $requestColumn;
        }
    }

    /**
     * @return boolean
     */
    public function hasSearch()
    {
        return $this->hasSearch;
    }


    public function getPage()
    {
        if (isset($this->start) && isset($this->length)) {
            return $this->start / $this->length + 1;
        } else {
            return 1;
        }
    }

    public function getMaxPerPage()
    {
        if (isset($this->length)) {
            if ($this->length == -1) {
                return 0;
            }
            return $this->length;
        } else {
            return Config::get('view.recordsPerPage.default');
        }

    }

    /**
     * @return Order[]
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return  Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }


}