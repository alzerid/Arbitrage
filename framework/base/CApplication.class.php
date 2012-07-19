<?
use \Arbitrage2\Base\CFileSearchLoader;

abstract class CApplication implements ISingleton, IErrorHandlerListener
{
	static private $_VERSION = "2.0.0";
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
		$this->requireFrameworkFile('Exceptions.class.php');                        //File full of base exception classes
		$this->requireFrameworkFile('Events.class.php');                            //Events

		//What do I do with these?
		$this->requireFrameworkFile('base/CErrorHandler.class.php');                //Exception handler and PHP error handler
		$this->requireFrameworkFile('base/CPropertyObject.class.php');              //Property Object class

		//Utils (array manipulator)
		$this->requireFrameworkFile('utils/CArrayManipulator.class.php');           //Array Manipulator
		$this->requireFrameworkFile('utils/CTemporaryCache.class.php');             //Property Object class
		$this->requireFrameworkFile('utils/CStringFormatter.class.php');            //String formatter

		//Helper
		$this->requireFrameworkFile('helper/Months.class.php');                     //Months formatter
		$this->requireFrameworkFile('helper/States.class.php');                     //States formatter

		//Templates
		$this->requireFrameworkFile('template/CTemplate.class.php');                //Template Class
		$this->requireFrameworkFile('template/CTemplateFile.class.php');            //Template File Class
		
		//Array Object
		$this->requireFrameWorkFile('array/CArrayObject.class.php');                //Array Object

		//Extended framework files
		$this->requireFrameworkFile('config/CArbitrageConfigLoader.class.php');     //Arbitrage config class
		$this->requireFrameworkFile('config/CArbitrageConfig.class.php');           //Arbitrage config class

		//Communication classes
		$this->requireFrameworkFile("communication/CEmailCommunication.class.php"); //Email communication class

		//Register exception handler
		CErrorHandler::getInstance()->addListener($this);

		//Database classes
		$this->requireFrameworkFile('database/CDatabaseDriverFactory.class.php');     //Require the Driver Factory

		//Remote cache
		$this->requireFrameworkFile("cache/remote/CRemoteCacheFactory.class.php");    //Remote cache factory (memcache, redis)

		//Get FileSearchLoader
		$this->requireFrameworkFile("base/CFileSearchLoader.class.php");

		//Autoload model handler
		spl_autoload_register('CApplication::modelAutoLoad', true, true);

		//Include extension base class
		$this->requireFrameworkFile('base/CExtension.class.php');
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
		$factory   = \Arbitrage2\Database\CDatabaseDriverFactory::getInstance();
		if($databases)
		{
			foreach($databases as $database => $list)
				$factory->load($database, $list);
		}

		//Add to application model path
		$this->addModelSearchPath(CArbitrageConfig::getInstance()->_internals->approotpath . "app/models/");
		
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
		$path = preg_replace('/\./', '/', $path);

		return strtolower($path);
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
