<?
//TODO: Code CCompenent class which can easy render files

class CApplication implements ISingleton, IErrorHandlerListener
{
	static private $_VERSION = "2.0.0";
	static private $_instance = NULL;    //Instance of CApplication

	private $_controller;         //The controller object that was requested
	private $_action;             //The action object that was requested

	public function __construct()
	{
		$this->_controller = NULL;
		$this->_action     = NULL;
	}

	static public function getInstance()
	{
		if(self::$_instance == NULL)
			self::$_instance = new CApplication();

		return self::$_instance;
	}

	/**
	 * Loads all the required files for the framework.
	 */
	public function bootstrap()
	{
		//Check for framework path
		if(!file_exists(ARBITRAGE_FW_PATH))
			die("Framework path is not defined in environment variable ARBITRAGE_FW_PATH");

		//Load base required framework files
		$this->requireFrameworkFile('Exceptions.class.php');                       //File full of base exception classes
		$this->requireFrameworkFile('Events.class.php');                           //Events
		$this->requireFrameworkFile('base/CApplication.class.php');                //Main application class that drive Arbitrage
		$this->requireFrameworkFile('base/CBaseController.class.php');             //Base controller class
		$this->requireFrameworkFile('base/CController.class.php');                 //Controller class
		$this->requireFrameworkFile('base/CAction.class.php');                     //Action class
		$this->requireFrameworkFile('base/renderers/CRenderer.class.php');         //Base renderer class
		$this->requireFrameworkFile('base/renderers/CViewFileRenderer.class.php'); //View File renderer class
		$this->requireFrameworkFile('base/renderers/CJSONRenderer.class.php');     //JSON Renderer
		$this->requireFrameworkFile('base/CFilterChain.class.php');                //Filter chain for CBaseControllers
		$this->requireFrameworkFile('base/CRouter.class.php');                     //Router handler
		$this->requireFrameworkFile('base/CErrorHandler.class.php');               //Exception handler and PHP error handler
		

		//Templates
		$this->requireFrameworkFile('template/CTemplate.class.php');                     //Template Class
		$this->requireFrameworkFile('template/CTemplateFile.class.php');                 //Template File Class


		//Array include
		$this->requireFrameWorkFile('array/CArrayObject.class.php');               //Array Object

		//Extended framework files
		$this->requireFrameworkFile('utils/URL.class.php');
		$this->requireFrameworkFile('config/CArbitrageConfig.class.php');          //Arbitrage config class

		//Register exception handler
		CErrorHandler::getInstance()->addListener($this);

		//Database classes
		$this->requireFrameworkFile('db/CModel.class.php');
		$this->requireFrameworkFile('db/CDBFactory.class.php');

		//HTML
		$this->requireFrameworkFile('base/CHTMLComponent.class.php');
		$this->requireFrameworkFile('form/Form.class.php');
		
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
				{
					$this->requireFrameworkFile("db/CMongoModel.class.php");
				}
				else
					throw new EArbitrageConfigException("Unknown database type '$db' in configuration file.");
			}
		}
		
		//Remote cache
		$this->requireFrameworkFile("cache/remote/CRemoteCacheFactory.class.php");
		CRemoteCacheFactory::initialize(CArbitrageConfig::getInstance()->arbitrage->remoteCache);

		//TODO: Local cache loading
		
		//Other stuff
		$this->requireFrameworkFile('utils/XMLDomConstruct.class.php');
		$this->requireFrameworkFile('utils/Curl.class.php');
		$this->requireFrameworkFile('utils/TemplateFile.class.php');
		$this->requireFrameworkFile('utils/HelperFunctions.php');




		//What to do with these guys?
		/*require_once($config->fwrootpath . 'include/FastLog.class.php');
		require_once($config->fwrootpath . 'include/Error.class.php');
		require_once($config->fwrootpath . 'include/ReturnMedium.class.php');
		require_once($config->fwrootpath . 'include/ArrayManipulator.class.php');
		require_once($config->fwrootpath . 'lib/database/MongoFactory.class.php');
		require_once($config->fwrootpath . 'lib/database/DB.class.php');
		Application::requireFrameworkFile('base/CArbitrageException.class.php');
		Application::requireFrameworkFile('config/CArbitrageConfig.class.php');
		Application::requireFrameworkFile('cache/CLocalCacheFactory.class.php');
		Application::requireFrameworkFile('logging/LogFacility.class.php');*/
	}

	public function run()
	{
		//TODO: Add a primary buffer layer then flush it
		//Parse URL and grab correct route
		$route = CRouter::route($_SERVER['REQUEST_URI']);

		//Get API class from Router
		$this->loadController($route);
			
		//Execute the action
		$this->_controller->execute();
	}

	/**
	 * Method loads the controller into memory and the action into memory.
	 * $param $controller The controller in route format to load.
	 */
	public function loadController($route)
	{
		$route = explode("/", $route);

		//Throw error if route is malformed
		if(count($route) < 2)
			throw new EArbitrageException("Unable to load controller because route is malformed '" . implode('/', $route) . "'.");

		$controller = strtolower($route[0]);
		$action     = strtolower($route[1]);

		//Require controller
		$this->requireApplicationController($controller);

		//Determine if we are an ajax call
		if(isset($_GET['_ajax']))
		{
			$this->requireApplicationAjaxController($controller);
			$controller = $controller . "AjaxController";
		}
		else
			$controller = $controller . "Controller";

		//Set Application controller to the new controller
		$this->_controller = new $controller();
		$this->_action     = new CAction($this->_controller, $route[1]);
		$this->_controller->setAction($this->_action);
	}

	/* Require File Methods */

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

	public function requireFrameworkFile($file)
	{
		require_once(ARBITRAGE_FW_PATH . "framework/$file");
	}

	public function requireApplicationController($controller)
	{
		//Load controller into memory
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/controllers/" . $controller . ".php";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to load controller '$controller' because it does not exist.");

		require_once($path);
	}

	public function requireApplicationAjaxController($controller)
	{
		//Load controller into memory
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/ajax/" . $controller . ".php";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to load ajax controller '$controller' because it does not exist.");

		require_once($path);
	}

	public function requireApplicationModel($model)
	{
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/models/$model.php";
		if(!file_exists($path))
			throw new EArbitrageException("Model '$model' does not exist.");

		require_once($path);
	}

	public function requireApplicationLibrary($lib)
	{
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/lib/$lib.php";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to include application library '$path'!");

		require_once($path);
	}

	/* END Require File Methods */

	static public function getConfig()
	{
		return CArbitrageConfig::getInstance();
	}

	static public function modelAutoLoad($class_name)
	{
		if(preg_match('/Model/', $class_name))
		{
			$class = strtolower(str_replace("Model", "", $class_name));
			CApplication::getInstance()->requireApplicationModel($class);
		}
	}


	/* IErrorHandlerListener  Methods */
	public function handleError(CErrorEvent $event)
	{
		//TODO: If debug is on, render
		$debug = CApplication::getConfig()->arbitrage->debugMode;
		if($debug === true)
		{
			//Flush output buffer
			ob_end_clean();

			//Render error
			$this->requireFrameworkFile('base/CFrameworkController.class.php');
			$controller = new CFrameworkController();
			$content    = $controller->render('errors/exception', array('event' => $event));

			//echo out the content
			echo $content;
			die();
		}
	}

	public function handleException(CExceptionEvent $event)
	{
		$debug = CApplication::getConfig()->arbitrage->debugMode;
		if($debug === true)
		{
			//Flush output buffer
			ob_end_clean();

			//Render error
			$this->requireFrameworkFile('base/CFrameworkController.class.php');
			$controller = new CFrameworkController();
			$content    = $controller->render('errors/exception', array('event' => $event));

			//echo out the content
			echo $content;
			die();
		}
		elseif($event->exception instanceof EHTTPException)
		{
			//Flush output buffer
			ob_end_clean();

			//Check to see which view we should show, arbitrage view or application views
			$this->requireFrameworkFile('base/CFrameworkController.class.php');
			$controller = new CFrameworkController();
			$content    = $controller->render('errors/http_' . $event->exception->getCode(), array('event' => $event));

			//Echo out the content
			echo $content;
			die();
		}
	}
	/* End Exception Listner Methods */
}
?>
