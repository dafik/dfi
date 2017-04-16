<?php
namespace Dfi\View\Helper\Navigation;

use RecursiveIteratorIterator;
use Zend_Navigation_Container;
use Zend_Navigation_Page;
use Zend_View_Helper_Navigation_Menu;

class Menu extends Zend_View_Helper_Navigation_Menu
{


    /**
     * Renders a normal menu (called from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container container to render
     * @param  string $ulClass CSS class for first UL
     * @param  string $indent initial indentation
     * @param  string $innerIndent inner indentation
     * @param  int|null $minDepth minimum depth
     * @param  int|null $maxDepth maximum depth
     * @param  bool $onlyActive render only active branch?
     * @param  bool $expandSibs render siblings of active
     *                                                  branch nodes?
     * @param  string|null $ulId unique identifier (id)
     *                                                  for first UL
     * @param  bool $addPageClassToLi adds CSS class from
     *                                                      page to li element
     * @param  string|null $activeClass CSS class for active
     *                                                      element
     * @param  string $parentClass CSS class for parent
     *                                                      li's
     * @param  bool $renderParentClass Render parent class?
     * @return string                                       rendered menu (HTML)
     */
    protected function _renderMenu(Zend_Navigation_Container $container,
                                   $ulClass,
                                   $indent,
                                   $innerIndent,
                                   $minDepth,
                                   $maxDepth,
                                   $onlyActive,
                                   $expandSibs,
                                   $ulId,
                                   $addPageClassToLi,
                                   $activeClass,
                                   $parentClass,
                                   $renderParentClass)
    {
        $html = '';

        // find deepest active
        if ($found = $this->findActive($container, $minDepth, $maxDepth)) {
            $foundPage = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
            RecursiveIteratorIterator::SELF_FIRST);
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            if (!$page->get('inMenu')) {
                continue;
            }
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            $t1 = $depth < $minDepth;
            $t2 = $this->accept($page, false);
            $t3 = $t1 || !$t2;

            if ($depth < $minDepth) {
                // page is below minDepth or not accepted by acl/visibilty
                continue;
            } else if (!$this->accept($page, false)) {
                // page is below minDepth or not accepted by acl/visibilty
                continue;
            } else if ($expandSibs && $depth > $minDepth) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($page->getParent()->isActive(true)) {
                        // page is a sibling of the active branch...
                        $accept = true;
                    }
                }
                if (!$isActive && !$accept) {
                    continue;
                }
            } else if ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages() ||
                            is_int($maxDepth) && $foundDepth + 1 > $maxDepth
                        ) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat($innerIndent, $depth * 2);


            if ($depth > $prevDepth) {
                $attribs = array();

                // start new ul tag
                if (0 == $depth) {
                    $attribs = array(
                        'class' => $ulClass,
                        'id' => $ulId,
                    );
                } else {
                    $attribs['class'] = 'sub-menu';

                }

                // We don't need a prefix for the menu ID (backup)
                $skipValue = $this->_skipPrefixForId;
                $this->skipPrefixForId();

                $html .= $myIndent . '<ul'
                    . $this->_htmlAttribs($attribs)
                    . '>'
                    . $this->getEOL();


                // Reset prefix for IDs
                $this->_skipPrefixForId = $skipValue;
            } else if ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    $ind = $indent . str_repeat($innerIndent, $i * 2);
                    $html .= $ind . $innerIndent . '</li>' . $this->getEOL();
                    $html .= $ind . '</ul>' . $this->getEOL();
                }
                // close previous li tag
                $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL();
            } else {
                // close previous li tag
                $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL();
            }

            // render li tag and page
            $liClasses = array();
            // Is page active?
            if ($isActive) {
                $liClasses[] = $activeClass;
            }
            // Add CSS class from page to LI?
            if ($addPageClassToLi) {
                $liClasses[] = $page->getClass();
            }
            // Add CSS class for parents to LI?
            if ($renderParentClass && $page->hasChildren()) {
                // Check max depth
                if ((is_int($maxDepth) && ($depth + 1 < $maxDepth))
                    || !is_int($maxDepth)
                ) {
                    $liClasses[] = $parentClass;
                }
            }

            $html .= $myIndent . $innerIndent . '<li'
                . $this->_htmlAttribs(array('class' => implode(' ', $liClasses)))
                . '>' . $this->getEOL()
                . $myIndent . str_repeat($innerIndent, 2)
                . $this->htmlify($page)
                . $this->getEOL();

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth + 1; $i > 0; $i--) {
                $myIndent = $indent . str_repeat($innerIndent . $innerIndent, $i - 1);
                $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL()
                    . $myIndent . '</ul>' . $this->getEOL();
            }
            $html = rtrim($html, $this->getEOL());
        }

        return $html;
    }


    public function htmlify(Zend_Navigation_Page $page)
    {
        // get label and title for translating
        $label = $page->getLabel();
        $title = $page->getTitle();

        // translate label and title?
        if ($this->getUseTranslator() && $t = $this->getTranslator()) {
            if (is_string($label) && !empty($label)) {
                $label = $t->translate($label);
            }
            if (is_string($title) && !empty($title)) {
                $title = $t->translate($title);
            }
        }

        // get attribs for element
        $attribs = array(
            'id' => $page->getId(),
            'title' => $title,
        );

        if (false === $this->getAddPageClassToLi()) {
            $attribs['class'] = $page->getClass();
        }

        // does page have a href?
        if ($href = $page->getHref()) {
            $element = 'a';
            $attribs['href'] = $href;
            $attribs['target'] = $page->getTarget();
            $attribs['accesskey'] = $page->getAccesskey();
        } else {
            $element = 'span';
        }

        // Add custom HTML attributes
        $attribs = array_merge($attribs, $page->getCustomHtmlAttribs());

        if ($page->get('icon') != null) {
            return '<' . $element . $this->_htmlAttribs($attribs) . '>'
            . '<i class="fa ' . $page->get('icon') . '"> '
            . '</i>'
            . $this->view->escape($label)
            . '</' . $element . '>';
        } else {

            return '<' . $element . $this->_htmlAttribs($attribs) . '>'
            . $this->view->escape($label)
            . '</' . $element . '>';
        }
    }

}

