<?php
namespace Dfi\View\Helper\DynamicForm;

class Map
{

    /**
     * @var array
     */
    protected $items;


    /**
     * @static
     * @return Map
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param array $items
     * @return Map
     */
    public function addItems($items = array())
    {
        /*$allowed = array(
            '\Dfi\View\Helper\DynamicForm\Callback',
            '\Dfi\View\Helper\DynamicForm\Map',
            'String'
        );*/

        foreach ($items as $key => $value) {
            $this->items[$key] = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}
