<?
class CWebApplication extends CApplication
{
	private $_controller;         //The controller object that was requested
	private $_action;             //The action object that was requested

	protected function __construct()
	{
		parent::__construct();

		$this->_controller = NULL;
		$this->_action     = NULL;
	}

	static public function getInstance()
	{
		if(self::$_instance == NULL)
			self::$_instance = new CWebApplication();

		return self::$_instance;
	}

	/**
	 * Loads all the required files for the framework.
	 */
	public function bootstrap()
	{
		parent::bootstrap();

		$this->requireFrameworkFile('base/CBaseController.class.php');             //Base controller class
		$this->requireFrameworkFile('base/CController.class.php');                 //Controller class
		$this->requireFrameworkFile('base/CAction.class.php');                     //Action class
		$this->requireFrameworkFile('base/renderers/CRenderer.class.php');         //Base renderer class
		$this->requireFrameworkFile('base/renderers/CViewFileRenderer.class.php'); //View File renderer class
		$this->requireFrameworkFile('base/renderers/CJSONRenderer.class.php');     //JSON Renderer
		$this->requireFrameworkFile('base/CFilterChain.class.php');                //Filter chain for CBaseControllers
		$this->requireFrameworkFile('base/CRouter.class.php');                     //Router handler
		$this->requireFrameworkFile('base/CFlashPropertyObject.class.php');        //Flash Property Object class

		//HTML
		$this->requireFrameworkFile('base/CHTMLComponent.class.php');
		$this->requireFrameworkFile('form/CForm.class.php');
	}

	/**
	 * Abstract function that is overloaded in order to run
	 * web applications.
	 */
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
		{
			if(CApplication::getConfig()->arbitrage->debugMode)
				throw new EArbitrageException("Unable to load controller because route is malformed '" . implode('/', $route) . "'.");
			else
				throw new EHTTPException(EHTTPException::$HTTP_BAD_REQUEST);
		}

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

	/**
	 * Method that requires a controller.
	 */
	public function requireApplicationController($controller)
	{
		//Load controller into memory
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/controllers/" . $controller . ".php";
		if(!file_exists($path))
		{
			if(CApplication::getConfig()->arbitrage->debugMode)
				throw new EArbitrageException("Unable to load controller '$controller' because it does not exist.");
			else
				throw new EHTTPException(EHTTPException::$HTTP_NOT_FOUND);
		}

		require_once($path);
	}

	/**
	 * Method that requires the AJAX controller.
	 */
	public function requireApplicationAjaxController($controller)
	{
		//Load controller into memory
		$path = CArbitrageConfig::getInstance()->_internals->approotpath . "app/ajax/" . $controller . ".php";
		if(!file_exists($path))
			throw new EArbitrageException("Unable to load ajax controller '$controller' because it does not exist.");

		require_once($path);
	}

	/* IErrorHandlerListener  Methods */
	public function handleError(CErrorEvent $event)
	{
		//TODO: If debug is on, render
		$debug = CApplication::getConfig()->arbitrage->debugMode;
		if($debug === true)
		{
			//Flush output buffer
			@ob_end_clean();

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
			@ob_end_clean();

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
			@ob_end_clean();

			//Check to see which view we should show, arbitrage view or application views
			$this->requireFrameworkFile('base/CErrorController.class.php');
			$controller = new CErrorController();
			$content    = $controller->render('http_' . $event->exception->getCode(), array('event' => $event));

			//Echo out the content
			echo $content;
			die();
		}
		else
		{
			$this->requireFrameworkFile('base/CErrorController.class.php');
			$controller = new CErrorController();
			$content    = $controller->render('http_500', array('event' => $event));

			//Echo out the content
			echo $content;
			die();
		}
	}
	/* End Exception Listner Methods */
}
?>
