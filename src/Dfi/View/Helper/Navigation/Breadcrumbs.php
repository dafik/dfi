<?php

class Dfi_View_Helper_Navigation_Breadcrumbs extends Zend_View_Helper_Navigation_Breadcrumbs

{


    // Render methods:

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper
     *
     * @param  Zend_Navigation_Container $container [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     */
    public function renderStraight(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        // find deepest active
        if (!$active = $this->findActive($container)) {
            return '';
        }

        $active = $active['page'];
        $parts = [];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html = $this->htmlify($active);
            if ($active->get('icon')) {
                $html = '<i class="fa ' . $active->get('icon') . '"></i> ' . $html;
            }
            $parts[] = '<li>' . $html . '</li>';
        } else {
            $html = $active->getLabel();
            if ($this->getUseTranslator() && $t = $this->getTranslator()) {
                $html = $t->translate($html);
            }
            $html = $this->view->escape($html);
            if ($active->get('icon')) {
                $html = '<i class="fa ' . $active->get('icon') . '"></i> ' . $html;
            }
            $parts[] = '<li>' . $html . '</li>';
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof Zend_Navigation_Page) {
                // prepend crumb to html
                $html = $this->htmlify($parent);
                if ($active->get('icon')) {
                    $html = '<i class="fa ' . $parent->get('icon') . '"></i> ' . $html;
                }
                $parts[] = '<li>' . $html . '</li>';
            }

            if ($parent === $container) {
                // at the root of the given container
                break;
            }

            $active = $parent;
        }

        //return strlen($html) ? $this->getIndent() . $html : '';

        $out = '<ul id="breadcrumbs" class="breadcrumb">';
        $out .= implode("\n", array_reverse($parts));
        $out .= '</ul>';

        return $out;
    }

}
