<?

class Dfi_Auth_Acl
{
	const FILE_ACL = 'configs/acl-conf.php';
	const FILE_MODULES = 'configs/modules-conf.php';


	public static function generateConfig()
	{
		self::generateAclMap();
		self::generateModuleMap();
	}
	private static function generateAclMap()
	{
		$roles = SysRoleQuery::create()->find();
		$map = array();

		foreach ($roles as $role) { /* @var $role SysRole */
			$map[$role->getId()] = $role->getEffectiveModules();
		}
		self::write(self::FILE_ACL,$map);
	}

	private static function generateModuleMap()
	{
		$modules = SysModuleQuery::create()
		->filterByModule(null,Criteria::ISNOTNULL)
		->filterByModule(null,Criteria::ISNOTNULL)
		->filterByModule(null,Criteria::ISNOTNULL)
		->find();
			
		$map = array();

		foreach ($modules as $module) { /* @var $module SysModule */
			if (!isset($map[$module->getModule()])) {
				$map[$module->getModule()] = array();
			}
			if (!isset($map[$module->getModule()][$module->getController()])) {
				$map[$module->getModule()][$module->getController()] = array();
			}
			$map[$module->getModule()][$module->getController()][$module->getAction()] = $module->getId();
		}
		self::write(self::FILE_MODULES,$map);
	}

	public static function getMapAcl()
	{
        /** @noinspection PhpIncludeInspection */
        $map = include self::FILE_ACL;
		return $map;
	}

	public static function getMapModules()
	{
		$map = include self::FILE_MODULES;
		return $map;
	}

	private static function write($file,$map)
	{
		$content = '<? return '.var_export($map,true).';';
		$location = APPLICATION_PATH . DIRECTORY_SEPARATOR.$file;

		$res = file_put_contents($location, $content);
		if (!$res) {
			throw new Exception('can\'t write '.$file);
		}

	}
	public static function getModulesIdsByRoleId($roleId)
	{
		$map = self::getMapAcl();
		if (isset($map[$roleId])) {
			return $map[$roleId];
		} else {
			return array();
		}
	}

	public static function getModulesIdsByRequest(Zend_Controller_Request_Abstract $request)
	{
		$map = self::getMapModules();
		if (isset($map[$request->getModuleName()][$request->getControllerName()][$request->getActionName()])) {
			return $map[$request->getModuleName()][$request->getControllerName()][$request->getActionName()];
		} else {
			return false;
		}
	}


}
