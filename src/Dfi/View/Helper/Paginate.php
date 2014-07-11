<?php

/**
 * Helper for rendering a pager footer.
 *
 */
class Dfi_View_Helper_Paginate extends Zend_View_Helper_Abstract
{


    public function paginate(PropelModelPager $pager, $paginateLink = null, $tplName = null)
    {

        if (0 == func_num_args()) {
            return $this;
        }

        /*if (null == $paginateLink) {
            $paginateLink = $this->view->url();
        }

        $paginateLink = preg_replace('/page\/[0-9]+/','', $paginateLink);
        $paginateLink = preg_replace('/\/$/','', $paginateLink);
*/

        $view = clone $this->view;
        //$view->assign('paginate',  $pager);
        //$view->assign('link', $paginateLink);

        if (null == $tplName) {
            $name = 'pagination_control.phtml';
        } else {
            $name = $tplName . '.phtml';
        }
        return $view->render($name);
    }
}