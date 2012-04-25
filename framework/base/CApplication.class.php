<?
abstract class CApplication implements ISingleton, IErrorHandlerListener
{
	static private $_VERSION = "2.0.0";
	static protected $_instance = NULL;    //Instance of CApplication
	protected $_model_path;

	protected function __construct()
	{
		$this->_model_path = array();
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
		$this->requireFrameworkFile('config/CArbitrageConfig.class.php');           //Arbitrage config class

		//Communication classes
		$this->requireFrameworkFile("communication/CEmailCommunication.class.php"); //Email communication class

		//Register exception handler
		CErrorHandler::getInstance()->addListener($this);

		//Database classes
		$this->requireFrameworkFile('db/CModel.class.php');
		$this->requireFrameworkFile('db/CDBFactory.class.php');

		
		//Autoload model handler
		spl_autoload_register('CApplication::modelAutoLoad', true, true);
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

		//Determine what db models we should load
		$databases = CArbitrageConfig::getInstance()->arbitrage->databases;
		if(count($databases))
		{
			foreach($databases as $db => $vals)
			{
				if(strtolower($db) === "mongo")
					$this->requireFrameworkFile("db/CMongoModel.class.php");
				else
					throw new EArbitrageConfigException("Unknown database type '$db' in configuration file.");
			}
		}

		//Add to application model path
		$this->addModelSearchPath(CArbitrageConfig::getInstance()->_internals->approotpath . "app/models/");
		
		//Remote cache
		$this->requireFrameworkFile("cache/remote/CRemoteCacheFactory.class.php");
		CRemoteCacheFactory::initialize(CArbitrageConfig::getInstance()->arbitrage->remoteCache);

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
	public function loadExtension($dir)
	{
		$path = realpath(ARBITRAGE2_FW_PATH . "/$dir");
		if($path === false)
			throw new EArbitrageException("Unable to load extension '$dir'.");

		//Get load order
		if(!file_exists("$path/.loadorder"))
			throw new EArbitrageException("Unable to find .loadorder config file for '$dir'.");

		$files = file_get_contents("$path/.loadorder");
		$files = explode(PHP_EOL, trim($files));
		foreach($files as $file)
		{
			if(!file_exists("$path/$file"))
				throw new EArbitrageException("Unable to load '$file' for extension '$dir'.");

			require_once("$path/$file");
		}
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
		foreach($this->_model_path as $path)
		{
			$path .= "$model.php";
			if(file_exists($path))
			{
				require_once($path);
				return;
			}
		}

		throw new EArbitrageException("Model '$model' does not exist.");
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
		if(!file_exists($path))
			throw new EArbitrageException("Model path '$path' does not exist!");

		$path = realpath($path) . '/';
		$this->_model_path[] = $path ;
	}

	static public function getConfig()
	{
		return CArbitrageConfig::getInstance();
	}

	static public function modelAutoLoad($class_name)
	{
		if(preg_match('/Model$/', $class_name))
		{
			$class = strtolower(str_replace("Model", "", $class_name));
			CApplication::getInstance()->requireApplicationModel($class);
		}
	}
}
?>
