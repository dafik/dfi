<?php
namespace Dfi\View\Helper\DynamicForm;
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.02.13
 * Time: 09:45
 * To change this template use File | Settings | File Templates.
 */
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
     * @internal param array $item
     */
    public function addItems($items = array())
    {
        $allowed = array(
            'Dfi_View_Helper_DynamicForm_Callback',
            'Dfi_View_Helper_DynamicForm_Map',
            'String'
        );

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
