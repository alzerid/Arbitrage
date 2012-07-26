<?
namespace Framework\Database;
use \Framework\Base\CService;

class CDatabaseService extends CService
{
	static protected $_SERVICE_TYPE = "database";

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
