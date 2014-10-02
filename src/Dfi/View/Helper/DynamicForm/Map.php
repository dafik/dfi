<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.02.13
 * Time: 09:45
 * To change this template use File | Settings | File Templates.
 */
class Dfi_View_Helper_DynamicForm_Map
{

    /**
     * @var array
     */
    protected $items;


    /**
     * @static
     * @return Dfi_View_Helper_DynamicForm_Map
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param array $item
     * @return Dfi_View_Helper_DynamicForm_Map
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
