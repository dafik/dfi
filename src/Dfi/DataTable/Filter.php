<?

namespace Dfi\DataTable;

use Exception;

class Filter
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var array
     */
    private $types = array(
        'text',
        'number',
        'date-range',
        'select'
    );

    /**
     * @var string
     */
    private $filterField;
    /**
     * @var string
     */
    private $filterOptions;

    /**
     * @param string $type
     * @throws Exception
     */
    public function __construct($type)
    {
        if (false === array_search($type, $this->types)) {
            throw new Exception('unsuported filter type: ' . $type);
        }

        $this->type = $type;
    }

    /**
     * @param string $type
     * @return Filter
     */
    public static function create($type)
    {
        return new Filter($type);
    }

    /**
     * @param string $filterField
     * @return $this
     */
    public function setFilterField($filterField)
    {
        $this->filterField = $filterField;
        return $this;
    }

    /**
     * @param [] $filterOptions
     * @return $this
     */
    public function setFilterOptions($filterOptions)
    {
        $this->filterOptions = $filterOptions;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilterOptions()
    {
        return $this->filterOptions;
    }


    /**
     * @return string
     */
    public function getFilterField()
    {
        return $this->filterField;
    }


    /**
     * @return boolean
     */
    public function hasFilterField()
    {
        return (boolean)$this->filterField;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


}