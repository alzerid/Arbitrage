<?
class CRemoteCacheFactory implements IModuleLoader
{
	static private $_INSTANCE = NULL;
	private $_search_paths;

	protected function __construct()
	{
		$this->_search_paths = array(dirname(realpath(__FILE__)));
	}

	static public function getInstance()
	{
		if(self::$_INSTANCE == NULL)
			self::$_INSTANCE = new CRemoteCacheFactory();

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
			if(file_exists($path . "/C$ucase.class.php"))
			{
				require_once($path . "/C$ucase.class.php");
				return;
			}
		}

		throw new CRemoteCacheException("Unable to load remote cache driver '$driver'.");
	}

	public function getHandle($driver, $opt)
	{
		throw new Exception("NOT IMPLEMENTED");
	}
}

class CRemoteCacheException extends Exception { }
?>
