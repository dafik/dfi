<?php
namespace Dfi;

use Dfi\Auth\Acl;
use Dfi\Iface\Helper;
use Dfi\Iface\Model\Sys\Module;
use Dfi\Iface\Model\Sys\User;
use Dfi\Iface\Provider\Sys\ModuleProvider;
use Zend_Acl;
use Zend_Auth;
use Zend_Controller_Front;
use Zend_Navigation;
use Zend_Navigation_Container;
use Zend_Navigation_Page;
use Zend_Navigation_Page_Mvc;
use Zend_Navigation_Page_Uri;
use Zend_Registry;
use Zend_View_Helper_Navigation;
use Zend_View_Helper_Navigation_Breadcrumbs;
use Zend_View_Helper_Navigation_Menu;
use Zend_View_Interface;

class Navigation
{
    /**
     * @var Zend_Navigation
     */
    private $navigation;

    /**
     * Enter description here ...
     * @var array
     */
    private $moduleConf;

    /**
     * Singelton instance
     *
     * @var Navigation
     */
    private static $_instance;

    private $isSetUp = false;


    private $pageTitle = false;
    private $pageSubTitle = false;

    private function __construct()
    {
        $this->createNavigation();
    }

    /**
     * Singelton constructor
     *
     * @return Navigation
     */
    public static function getInstance()
    {
        if (self::$_instance instanceof Navigation) {
            return self::$_instance;
        }
        return self::$_instance = new Navigation();
    }

    private function createNavigation()
    {

        $this->moduleConf = Acl::getMapModules();
        $nav = New Zend_Navigation();

        $providerName = Helper::getClass("iface.provider.sys.module");
        /** @var ModuleProvider $provider */
        $provider = $providerName::create();


        $modules = $provider
            ->filterByTreeLevel(1)
            ->orderByTreeLeft()->find();
        foreach ($modules as $module) {
            /* @var $module Module */
            $page = $this->createPage($module);
            $nav->addPage($page);
            if ($module->hasChildren()) {
                if ($module->countChildren() > 1) {
                    $this->addChildren($page, $module);
                } else {
                    $child = $module->getFirstChild();
                    //$page->set
                }
            }
        }

        $this->navigation = $nav;


    }

    /**
     * @param Module $module
     * @return Zend_Navigation_Page
     */
    private function createPage(Module $module)
    {
        $resourceId = $module->getId();
        if ($module->isMvcPage()) {

            $page = new Zend_Navigation_Page_Mvc();
            $page->setLabel($module->getName());
            $page->setOptions(array(
                'icon' => $module->getIcon(),
                'title' => $module->getTitle(),
                'subtitle' => $module->getSubTitle(),
                'inMenu' => $module->getInMenu()
            ));

            $page->setModule($module->getModule());
            $page->setController($module->getController());
            $page->setAction($module->getAction());


            //$page->setClass('ui-state-default ui-corner-top');
        } else {
            $page = new Zend_Navigation_Page_Uri();
            $page->setOptions(array(
                'icon' => $module->getIcon(),
                'title' => $module->getTitle(),
                'subtitle' => $module->getSubTitle(),
                'inMenu' => $module->getInMenu()
            ));

            $page->setLabel($module->getName());
            $page->setActive(false);
            //$page->setClass('ui-state-default ui-corner-top');
            $page->setUri('javascript:void(0);');
        }

        if ($resourceId) {
            $page->setResource((string)$resourceId);
        }
        return $page;
    }

    private function addChildren(Zend_Navigation_Container $nav, Module $module)
    {

        $children = $module->getChildren();
        /* @var $module Module */
        foreach ($children as $module) {

            $page = $this->createPage($module);

            if ($module->hasChildren()) {
                if ($module->countChildren() > 1) {
                    $this->addChildren($page, $module);
                } else {

                    /** @var Module $child */
                    $child = $module->getFirstChild();
                    $page = $this->createPage($child);
                }
            }

            $nav->addPage($page);
            if ($page->isActive()) {
                $nav->setActive(true);
            }

        }
    }

    public function replaceList($html)
    {

        $html = str_replace('<ul', '<div', $html);
        $html = str_replace('</ul', '</div', $html);

        $html = str_replace('<li', '<div', $html);
        $html = str_replace('</li', '</div', $html);

        return $html;
    }

    private function setUp(Zend_View_Interface $view)
    {

        if (!$this->isSetUp) {
            $helperName = 'Dfi_View_Helper_Navigation';
            $view->addHelperPath(_BASE_PATH . 'vendor/dafik/dfi/src/' . str_replace('_', '/', $helperName), $helperName);
        }

        /** @var $helper Zend_View_Helper_Navigation */
        $helper = $view->getHelper('navigation');

        if (!$this->isSetUp) {

            $helper->navigation($this->navigation);


            $aclPlugin = Zend_Controller_Front::getInstance()->getPlugin('Dfi_Controller_Plugin_Acl');

            if ($aclPlugin) {

                $acl = Zend_Registry::get('acl');
                /* @var $acl Zend_Acl */
                $identity = Zend_Auth::getInstance()->getIdentity();
                /* @var $identity User */

                if ($acl && $identity) {
                    ;
                    /* @var $helper Zend_View_Helper_Navigation_Menu */
                    $helper->setAcl($acl);
                    $helper->setRole((string)$identity->getRoleId());
                }
            }
            $this->isSetUp = true;
        }

        return $helper;
    }


    public function renderMenu(Zend_View_Interface $view)
    {
        /** @var $helper Zend_View_Helper_Navigation */
        $helper = $this->setUp($view);

        $menu = $helper->menu()
            ->setUlId('nav')
            ->setActiveClass('current open');

        $menu->render();
        //'current open'

        return $menu;

    }

    public function renderBreadCrumbs(Zend_View_Interface $view)
    {
        /** @var $helper Zend_View_Helper_Navigation */
        $helper = $this->setUp($view);

        /** @var $bread Zend_View_Helper_Navigation_Breadcrumbs */
        $bread = $helper->breadcrumbs();
        $breadcrumbs = $bread->render();


        return $breadcrumbs;

    }

    public function renderPageTitle(Zend_View_Interface $view)
    {
        /** @var $helper Zend_View_Helper_Navigation */
        $helper = $this->setUp($view);

        $container = $helper->getContainer();


        // find deepest active
        if (!$active = $helper->findActive($container)) {
            return '';
        }

        /** @var Zend_Navigation_Page $active */
        $active = $active['page'];

        $title = $this->pageTitle ? $this->pageTitle : $active->get('title');
        $subtitle = $this->pageSubTitle ? $this->pageSubTitle : $active->get('subtitle');

        if (!$title) {
            $title = $active->getLabel();
            $subtitle = $active->get('module') . ' - ' . $active->get('controller') . ' - ' . $active->get('action');
        }

        return ['title' => $title, 'subtitle' => $subtitle];

    }

    /**
     * @param boolean $pageSubTitle
     */
    public function setPageSubTitle($pageSubTitle)
    {
        $this->pageSubTitle = $pageSubTitle;
    }

    /**
     * @param boolean $pageTitle
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }


}