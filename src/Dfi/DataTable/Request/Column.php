<?
namespace Dfi\DataTable\Request;

class Column
{
    private $data;
    private $name;
    private $searchable;
    private $orderable;
    private $search;


    public function __construct($data, $name, $searchable, $orderable, $search)
    {
        $this->data = $data;
        $this->name = $name;
        $this->searchable = filter_var($searchable, FILTER_VALIDATE_BOOLEAN);
        $this->orderable = filter_var($orderable, FILTER_VALIDATE_BOOLEAN);
        $this->search = new ColumnSearch($search['regex'], $search['value']);
    }

    public function hasSearch()
    {
        return $this->search->hasSearch();
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @return string
     */
    public function getSearchValue()
    {
        return $this->search->getValue();
    }

    public function getSearchOperator()
    {
        return $this->search->getOperator();
    }


}
