<?
class CDatabaseDriverFactory implements IModuleLoader
{
	static private $_INSTANCE = NULL;
	private $_databas_cfg;
	private $_search_paths;
	private $_path;
	
	protected function __construct()
	{
		//Get path
		$this->_path         = dirname(realpath(__FILE__));
		$this->_search_paths = array($this->_path);
		$this->_databas_cfg  = array();

		//Require each class
		require_once($this->_path . "/EModelException.class.php");
		require_once($this->_path . "/CModelResults.class.php");
		require_once($this->_path . "/CModelData.class.php");
		require_once($this->_path . "/CModelArrayData.class.php");
		require_once($this->_path . "/CModelHashData.class.php");
		require_once($this->_path . "/CModelQuery.class.php");
		require_once($this->_path . "/CModelBatch.class.php");
		require_once($this->_path . "/CModel.class.php");
	}

	static public function getInstance()
	{
		if(self::$_INSTANCE == NULL)
			self::$_INSTANCE = new CDatabaseDriverFactory();

		return self::$_INSTANCE;
	}

	public function registerPath($path)
	{
		$this->_search_paths[] = $path;
	}

	public function load($driver, $config)
	{
		$ucase = ucwords($driver);
		foreach($this->_search_paths as $path)
		{
			if(!file_exists($path . "/$driver"))
				continue;

			require_once($path . "/$driver/C{$ucase}Driver.class.php");
			require_once($path . "/$driver/C{$ucase}ModelQuery.class.php");
			require_once($path . "/$driver/C{$ucase}ModelBatch.class.php");
			require_once($path . "/$driver/C{$ucase}ModelResults.class.php");

			//Set database options
			$this->_database_cfg[$driver] = $config;
			return;
		}

		//Throw error that we could not find the driver
		throw new CDatabaseDriverException("Unknown driver '$driver'.");
	}

	public function getHandle($driver, $config)
	{
		$driver = "C" . ucwords($type) . "Driver";
		var_dump("GET Database", $driver, $config);
		die();
		$handle = $driver::getHandle($config);

		var_dump($handle);
		die();

		return $handle;
	}

}

abstract class CDatabaseDriver
{
	static public function getHandle($config)
	{
		throw new CDatabaseDriverException("Your driver must implement ::getHandle.");
	}
}

class CDatabaseDriverException extends Exception { }
?>
