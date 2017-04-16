<?

namespace Dfi\Controller\Action\Helper;

use Criteria;
use Dfi\Auth\Acl as AuthAcl;
use Dfi\Auth\Storage\Cookie;
use Dfi\Iface\Helper;
use Dfi\Iface\Model\Sys\Module;
use Dfi\Iface\Model\Sys\User;
use Dfi\Iface\Provider\Sys\ModuleProvider;
use Exception;
use Zend_Acl;
use Zend_Acl_Resource;
use Zend_Acl_Role;
use Zend_Auth;
use Zend_Controller_Action_Helper_Abstract;
use Zend_Registry;

class Acl extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Acl
     *
     */
    private $acl;


    /**
     * roleName
     *
     * @var
     */
    private $roleName;
    private $roleId;

    public function __construct()
    {
        $this->acl = new Zend_Acl();

    }

    public function init($modelName)
    {
        parent::init();

        $this->setRoles($modelName);
        $this->setResources();
        $this->setPrivilages();
        $this->setAcl();
    }

    private function setRoles($modelName)
    {
        Zend_Auth::getInstance()->setStorage(new Cookie($modelName));
        if (Zend_Auth::getInstance()->hasIdentity()) {

            /* @var $user User */
            $user = Zend_Auth::getInstance()->getIdentity();


            $this->roleName = $user->getSysRole()->getName();
            $this->roleId = $user->getSysRole()->getId();
            $this->acl->addRole(new Zend_Acl_Role($this->roleId));
        }

    }

    private function setResources()
    {
        //TODO from file

        $providerName = Helper::getClass("iface.provider.sys.module");
        /** @var ModuleProvider $provider */
        $provider = $providerName::create();


        $modules = $provider->filterByTreeLevel(0, Criteria::GREATER_THAN)->find();
        //$this->acl->add(new Zend_Acl_Resource('index'));
        foreach ($modules as $module) {
            /* @var $module Module */
            $this->acl->addResource(new Zend_Acl_Resource($module->getId()));
        }
    }

    private function setPrivilages()
    {

        if (Zend_Auth::getInstance()->hasIdentity()) {
            //$privilages = AclQuery::create()->filterByRoleId($this->roleId)->find();
            //$privileges = array();
            $userPrivileges = AuthAcl::getModulesIdsByRoleId($this->roleId);
            $userPrivileges = $this->checkResources($userPrivileges);
            //$userPrivilages = array();
            if ($userPrivileges) {
                $this->acl->allow($this->roleId, $userPrivileges);
            }
        }
    }

    private function checkResources($resources)
    {
        $acl = $this->acl;
        $filtered = array();
        foreach ($resources as $resource) {
            try {
                if ($acl->get($resource)) {
                    $filtered[] = $resource;
                }
            } catch (Exception $e) {
                //TODO $x = 1;
            }
        }
        return $filtered;
    }

    private function setAcl()
    {
        Zend_Registry::set('acl', $this->acl);
    }


}