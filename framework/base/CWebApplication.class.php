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

		//Renderers and Renderables
		$this->requireFrameworkFile('base/renderables/CHTMLRenderable.class.php');
		$this->requireFrameworkFile('base/renderables/CTextRenderable.class.php');
		$this->requireFrameworkFile('base/renderables/CJSONRenderable.class.php');
		$this->requireFrameworkFile('base/renderables/CJSONApplicationRenderable.class.php');
		$this->requireFrameworkFile('base/renderables/CJSONClientMVCRenderable.class.php');
		$this->requireFrameworkFile('base/renderables/CJavascriptRenderable.class.php');
		$this->requireFrameworkFile('base/renderables/CViewFilePartialRenderable.class.php');
		$this->requireFrameworkFile('base/renderables/CViewFileRenderable.class.php');

		//Controller, Actions, etc...
		$this->requireFrameworkFile('base/CBaseController.class.php');             //Base controller class
		$this->requireFrameworkFile('base/CController.class.php');                 //Controller class
		$this->requireFrameworkFile('base/CAction.class.php');                     //Action class
		$this->requireFrameworkFile('base/CFilterChain.class.php');                //Filter chain for CBaseControllers
		$this->requireFrameworkFile('base/CRouter.class.php');                     //Router handler
		$this->requireFrameworkFile('base/CFlashPropertyObject.class.php');        //Flash Property Object class

		//HTML
		$this->requireFrameworkFile('html/CHTMLComponent.class.php');
		$this->requireFrameworkFile('form/CForm.class.php');
		$this->requireFrameworkFile('form/CSubmittedForm.class.php');
		$this->requireFrameworkFile('form/CRendererForm.class.php');

		//HTML Data Table
		//$this->requireFrameworkFile('html/CHTMLDivDataTable.class.php');
		$this->requireFrameworkFile('html/CHTMLDataTable.class.php');
		$this->requireFrameworkFile('html/CHTMLDataTableModel.class.php');
		$this->requireFrameworkFile('html/dataentry/CHTMLImageDataEntry.class.php');

		//Autoload model handler
		spl_autoload_register('CForm::autoLoad', true);
	}

	/** 
	 * Initializes the web application by loading its config file
	 * and determining which other framework classes to require.
	 * @path string The path to the application configuration file.
	 */
	public function initialize($path="config/config.php")
	{
		//Parent initialize
		parent::initialize();

		//Load additional files for Client MVC if exists
		$config = $this->getConfig();
		if(isset($config->client) && isset($config->client->mvc) && $config->client->mvc->serverCanvas)
		{
			$this->requireFrameworkFile('base/renderables/CJSONClientMVCRenderable.class.php');
			//$this->requireFrameworkFile('renderables/CXMLClientMVCRenderable.class.php'); //XML
		}
	}


	/**
	 * Abstract function that is overloaded in order to run
	 * web applications.
	 */
	public function run()
	{
		//Parse URL and grab correct route
		$route = CRouter::route($_SERVER['REQUEST_URI']);

		//Get Controller class from Router
		$this->loadController($route, isset($_REQUEST['_ajax']));
			
		//Execute the action
		$this->_controller->execute();
	}

	/**
	 * Method that actually executes the action within the Controller context via a route rule.
	 * param $forward The route to use to forward execution.
	 * param $ajax Determine if we should load the ajax controller.
	 */
	public function forward($forward, $ajax=false)
	{
		static $controllers = array();
		static $actions     = array();
		static $renderables = array();

		//Get old controller and action
		$controllers[] = $this->_controller;
		$actions[]     = $this->_action;
		$renderables[] = $this->_controller->getRenderer();

		//New CViewFileRenderable
		$this->_controller->setRenderer('CViewFileRenderable');

		//Get routing rules
		$route = CRouter::route($forward);

		//Get Controller Class
		$this->loadController($route, $ajax);

		//Execute the controller
		$ret = $this->_controller->execute(false);

		//Set back old action/controller
		$this->_controller = array_pop($controllers);
		$this->_action     = array_pop($actions);
		$this->_controller->setRenderer(array_pop($renderables));

		return $ret;
	}

	/**
	 * Method loads the controller into memory and the action into memory.
	 * param $controller The controller in route format to load.
	 * param $ajax Determins if we should load the ajax controller.
	 */
	public function loadController($route, $ajax=false)
	{
		$route = explode("/", $route);

		//Throw error if route is malformed
		if(count($route) < 2)
		{
			if(CApplication::getConfig()->server->debugMode)
				throw new EArbitrageException("Unable to load controller because route is malformed '" . implode('/', $route) . "'.");
			else
				throw new EHTTPException(EHTTPException::$HTTP_BAD_REQUEST);
		}

		$controller = strtolower($route[0]);
		$action     = strtolower($route[1]);

		//Require controller
		$this->requireApplicationController($controller);

		//Determine if we are an ajax call
		if($ajax)
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
		$this->_controller->setAjax($ajax);
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
			if(CApplication::getConfig()->server->debugMode)
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

	/**
	 * Returns the current controller.
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/* IErrorHandlerListener  Methods */
	public function handleError(CErrorEvent $event)
	{
		//TODO: If debug is on, render
		$debug = CApplication::getConfig()->server->debugMode;
		if($debug === true)
		{
			//Flush output buffer
			@ob_end_clean();

			//Render error
			$this->requireFrameworkFile('base/CFrameworkController.class.php');
			$controller = new CFrameworkController();
			$content    = $controller->renderContent('errors/exception', array('event' => $event));

			//echo out the content
			echo $content;
			die();
		}
	}

	public function handleException(CExceptionEvent $event)
	{
		$debug = CApplication::getConfig()->server->debugMode;
		if($debug === true)
		{
			//Flush output buffer
			@ob_end_clean();

			//Render error
			$this->requireFrameworkFile('base/CFrameworkController.class.php');
			$controller = new CFrameworkController();
			$content    = $controller->renderContent('errors/exception', array('event' => $event));

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
			$this->_controller = new CErrorController('http_' . $event->exception->getCode(), array('event' => $event));
			$this->_action     = new CAction($this->_controller, 'process');
			$this->_controller->setAction($this->_action);
			$this->_controller->execute();

			die();
		}
		else
		{
			//Flush output buffer
			@ob_end_clean();

			//Check to see which view we should show, arbitrage view or application views
			$this->requireFrameworkFile('base/CErrorController.class.php');
			$this->_controller = new CErrorController('http_500', array('event' => $event));
			$this->_action     = new CAction($this->_controller, 'process');
			$this->_controller->setAction($this->_action);
			$this->_controller->execute();

			die();
		}
	}
	/* End Exception Listner Methods */
}
?>
