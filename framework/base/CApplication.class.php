<?
namespace Arbitrage2\Base;
use \Arbitrage2\Interfaces\ISingleton;
use \Arbitrage2\Interfaces\IErrorHandlerListener;

use \Arbitrage2\Base\CWebApplication;
use \Arbitrage2\DAtabase\CDatabaseDriverFActory;
use \Arbitrage2\Config\CArbitrageConfig;
use \Arbitrage2\Utils\CFileSearchLoader;
use \Arbitrage2\Exceptions\EArbitrageException;

abstract class CApplication implements ISingleton, IErrorHandlerListener
{
	static private $_VERSION = "2.0.0";
	static private $_FS_DELIMETER = "/";
	static protected $_instance = NULL;    //Instance of CApplication

	protected $_model_search;
	protected $_extensions;

	protected function __construct()
	{
		$this->_extensions   = array();
	}

	static public function getInstance()
	{
		return self::$_instance;
	}

	/**
	 * Method creates a CWebApplication instance of CApplication.
	 */
	static public function createWebApplication()
	{
		//Check for framework path
		if(!file_exists(ARBITRAGE2_FW_PATH))
			die("Framework path is not defined in environment variable ARBITRAGE2_FW_PATH");

		self::requireFrameworkFile('base/CWebApplication.class.php');
		self::$_instance = new CWebApplication();
	}

	/**
	 * Method creates a CCLIApplication instance of CApplication.
	 */
	static public function createCLIApplication()
	{
		//Check for framework path
		if(!file_exists(ARBITRAGE2_FW_PATH))
			die("Framework path is not defined in environment variable ARBITRAGE2_FW_PATH");

		self::requireFrameworkFile('cli/CCLIApplication.class.php');
		self::$_instance = new CCLIApplication();
	}

	/**
	 * Loads all the required files for the framework.
	 */
	public function bootstrap()
	{
		//Check for framework path
		if(!file_exists(ARBITRAGE2_FW_PATH))
			die("Framework path is not defined in environment variable ARBITRAGE2_FW_PATH");

		//Load base required framework files
		$this->requireFramework('Exceptions');                           //File full of base exception classes
		$this->requireFramework('Events');                               //Events
		$this->requireFramework('ErrorHandler.CErrorHandlerObserver');   //Error Handler Observer
		
		//Array Object
		$this->requireFrameWork('Utils.CArrayObject');                //Array Object

		//Extended framework files
		$this->requireFramework('Config.CArbitrageConfigLoader');
		$this->requireFramework('Config.CArbitrageConfig');

		//Templates
		//$this->requireFrameworkFile('template/CTemplate.class.php');                //Template Class
		//$this->requireFrameworkFile('template/CTemplateFile.class.php');            //Template File Class

		//Communication classes
		//$this->requireFrameworkFile("communication/CEmailCommunication.class.php"); //Email communication class


		$this->requireFramework('Database.CDatabaseDriverFactory');                   //Require the Driver Factory
		$this->requireFramework("Cache.Remote.CRemoteCacheFactory");                  //Remote cache factory (memcache, redis)
		$this->requireFramework("Utils.CFileSearchLoader");                           //File loader
		$this->requireFramework("Base.CExtension");                                   //Load the extension class

		//Autoload model handler
		spl_autoload_register(__NAMESPACE__ . '\CApplication::modelAutoLoad', true, true);
	}

	/** 
	 * Initializes the web application by loading its config file
	 * and determining which other framework classes to require.
	 * @path string The path to the application configuration file.
	 */
	public function initialize($path="config/config.php")
	{
		//Load application config
		$this->loadApplicationConfig($path);

		//Setup search paths
		$this->_model_search = new CFileSearchLoader;

		//Get config
		$config = CArbitrageConfig::getInstance();

		//Load correct drivers
		$databases = $config->server->databases;
		$factory   = CDatabaseDriverFactory::getInstance();
		if($databases)
		{
			foreach($databases as $database => $list)
				$factory->load($database, $list);
		}

		//Add to application model path
		$this->addModelSearchPath(CArbitrageConfig::getInstance()->_internals->approotpath . "application/models/");
		
		//Remote cache
		$remotes = $config->server->remoteCache;
		$factory = CRemoteCacheFactory::getInstance();
		if($remotes)
		{
			foreach($remotes as $remote => $list)
				$factory->load($remote, $list);
		}

		//TODO: Local cache loading
		
		//Other stuff
		$this->requireFrameworkFile('utils/XMLDomConstruct.class.php');
		$this->requireFrameworkFile('utils/Curl.class.php');
		$this->requireFrameworkFile('utils/HelperFunctions.php');
		$this->requireFrameworkFile('utils/URL.class.php');
	}

	/*
	 * Load extension
	 */
	public function loadExtension($namespace)
	{
		if(isset($this->_extensions[$namespace]))
			return;

		//Setup path
		$path = $this->convertNamespaceToPath($namespace);
		$path = realpath($this->getConfig()->_internals->approotpath . "/app/extensions/$path");
		if($path === false)
			throw new EArbitrageException("Unable to load extension '$namespace'.");

		//Require the extension file
		require_once("$path/extension.php");

		//Load extension
		$class = $this->convertNamespaceToPHP($namespace) . "\\Extension";
		$this->_extensions[$namespace] = new $class($path, $namespace);
		$this->_extensions[$namespace]->initialize();
	}
	
	/*
	 * Abstract run function that is called to run
	 * the application after initialization and
	 * bootstrap.
	 */
	abstract public function run();


	/** Require File Methods **/

	/** 
	 * Loads the application configuration file.
	 * @path string The path to the application configuration file.
	 */
	public function loadApplicationConfig($path="config/config.php")
	{
		if(file_exists($path))
			require_once($path);
		else
			throw new EArbitrageException("Unable to load application configuration file.");
	}

	static public function requireFramework($namespace)
	{
		$path = ARBITRAGE2_FW_PATH . "/framework";

		//Convert namespace
		$ns   = explode('.', $namespace);
		$file = array_splice($ns, count($ns)-1);
		$file = $file[0];

		//Convert namespace to directory formatting
		$ns   = implode('/', $ns);
		$ns   = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $ns);
		$ns   = strtolower(preg_replace('/ /', '_', $ns));
		$path = "$path/$ns/$file.class.php";

		if(!file_exists($path))
			throw new EArbitrageException("Unable to require namespace '$namespace'.");

		require_once($path);
	}

	static public function requireFrameworkFile($file)
	{
		require_once(ARBITRAGE2_FW_PATH . "framework/$file");
	}

	public function requireApplicationModel($model)
	{
		$this->_model_search->loadFile("$model.php");
	}

	public function requireApplicationLibrary($lib)
	{
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/lib/$lib.php";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to include application library '$path'!");

		require_once($path);
	}
	/** END Require File Methods **/

	public function addModelSearchPath($path)
	{
		$this->_model_search->addPath($path);
	}

	public function addViewSearchPath($path)
	{
		CViewFilePartialRenderable::addViewPath($path);
	}

	static public function convertNamespaceToPath($namespace)
	{
		$path = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $namespace);
		$path = preg_replace('/ /', '_', $path);
		$path = preg_replace('/\./', self::$_FS_DELIMETER, $path);

		return $path;
	}

	static public function convertNamespaceToPHP($namespace)
	{
		return "\\" . preg_replace('/\./', '\\', $namespace);
	}

	static public function convertPHPNamespaceToPath($namespace)
	{
		$path = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $namespace);
		$path = preg_replace('/ /', '_', $path);
		$path = preg_replace('/\\\/', '/', $path);

		return strtolower($path);
	}

	static public function getConfig()
	{
		return CArbitrageConfig::getInstance();
	}

	static public function modelAutoLoad($class_name)
	{
		if(preg_match('/Model$/', $class_name))
		{
			$class = preg_replace('/Model$/', '', $class_name);
			$class = self::convertPHPNamespaceToPath($class);
			CApplication::getInstance()->requireApplicationModel($class);
		}
	}
}
?>
