<?
namespace Framework\Database;
use \Framework\Base\CService;

class CDatabaseService extends CService
{
	static protected $_SERVICE_TYPE = "database";   //Service type
	protected $_db_config;                          //Database configuration

	/**
	 * Method initializes the service.
	 */
	public function initialize()
	{
		//Require base service classes
		$this->requireServiceFile("EModelException");
		$this->requireServiceFile("CModelResults");
		$this->requireServiceFile("CModelData");
		$this->requireServiceFile("CModelArrayData");
		$this->requireServiceFile("CModelHashData");
		$this->requireServiceFile("CModelQuery");
		$this->requireServiceFile("CModelBatch");
		$this->requireServiceFile("CModel");
		$this->requireServiceFile("CDatabaseDriver");

		//TODO: Load necessary drivers
		$this->_db_config = array();
		$config           = $this->getConfig();
		$loaded           = array();
		foreach($config as $key => $val)
		{
			//Load driver if not loaded
			if(!in_array($val['driver'], $loaded))
			{
				//Load the driver
				$namespace = ucwords($val['driver']);
				$this->requireServiceFile("$namespace.C{$namespace}Driver");
				$this->requireServiceFile("$namespace.C{$namespace}ModelQuery");
				$this->requireServiceFile("$namespace.C{$namespace}ModelBatch");
				$this->requireServiceFile("$namespace.C{$namespace}ModelResults");

				$loaded[] = $val['driver'];
			}
		}

		//Setup model autoload
		spl_autoload_register(__NAMESPACE__ . '\CDatabaseService::modelAutoLoad', true, true);
	}

	/**
	 * Method autoloads model files.
	 */
	static public function modelAutoLoad($class)
	{
		//Make sure we are model autoloading
		if(preg_match('/Model$/', '', $class))
		{
			$file = preg_replace('/Model$/', '', $class);
			var_dump($file);
			die();
		}
	}
}
?>
