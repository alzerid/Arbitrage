<?
namespace Framework\Base;
use \Framework\Exceptions\EWebApplicationException;

class CWebApplication extends CApplication
{
	private $_request_uri;         //Request URI
	private $_virtual_uri;         //Virtual URI (translated URI)
	private $_renderable_paths;    //Paths where the renderables exist
	private $_controller_queue;    //Controller queue
	private $_router;              //Router instance

	/**
	 * Initializes the arbitrage application, loads the application config.
	 * @param string $path The path where the application resides in.
	 * @param string $namespace The namespace associated with the object.
	 */
	public function initialize()
	{
		//Setup variables
		$this->_real_uri         = '';
		$this->_virtual_uri      = '';
		$this->_renderable_paths = array();
		$this->_controller_queue = array();
		$this->_router           = NULL;

		//Call parent
		parent::initialize();

		//Require framework files
		CKernel::getInstance()->requireFrameworkFile("Base.CController");
		CKernel::getInstance()->requireFrameworkFile("Base.CJavascriptController");
		CKernel::getInstance()->requireFrameworkFile("Base.CAction");
		CKernel::getInstance()->requireFrameworkFile('Base.CRouter');
		CKernel::getInstance()->requireFrameworkFile('Base.CFilterChain');
		CKernel::getInstance()->requireFrameworkFile('Base.CWebPackage');
		CKernel::getInstance()->requireFrameworkFile('Utils.CFlashPropertyObject');
		CKernel::getInstance()->requireFrameworkFile('DOM.CDOMGenerator');
		CKernel::getInstance()->requireFrameworkFile('Form.CFormModel');
		CKernel::getInstance()->requireFrameworkFile('Form.CForm');
		CKernel::getInstance()->requireFrameworkFile('Form.CSubmittedForm');
		CKernel::getInstance()->requireFrameworkFile('Form.CRenderableForm');

		//Create router instance and route
		$this->_router      = new CRouter($this->getConfig()->webApplication->routes);
		$this->_request_uri = $_SERVER['REQUEST_URI'];
		$this->_virtual_uri = $this->_router->route($_SERVER['REQUEST_URI']);

		//Create relavent services
		$this->_initializeServices();

		//Initialize Packages
		$this->_initializePackages();
	}

	/** 
	 * Method runs the Web Application
	 */
	public function run()
	{
		//Route again
		$this->_virtual_uri = $this->_router->route($_SERVER['REQUEST_URI']);

		//Load the controller
		$controller = $this->loadController($this->_virtual_uri, isset($_REQUEST['_ajax']));

		//Execute the action
		$ret = $controller->execute();

		//Render the return
		$controller->render($ret);
	}

	/**
	 * Method returns the virtual URI.
	 * @return string Returns the virtual URI.
	 */
	public function getVirtualURI()
	{
		return $this->_virtual_uri;
	}

	/**
	 * Method returns the request URI.
	 * @return string Retuns the request URI.
	 */
	public function getRequestURI()
	{
		return $this->_request_uri;
	}

	/**
	 * Method returns the current routes for the application.
	 * @return \Framework\Config\CArbitrageConfigProperty Returns the routes.
	 */
	public function getRoutes()
	{
		return $this->getConfig()->webApplication->routes;
	}

	/**
	 * Method loads the controller.
	 * @param string $route URL formatted route that specifies the controller.
	 * @param boolean $ajax Determines if the controller is an AJAX controller.
	 * @return Returns the controller.
	*/
	public function loadController($route, $ajax=false)
	{
		//Add the Controllers namespace
		$url    = explode('/', preg_replace('/\?.*$/', '', $route));
		$action = $url[count($url)-1];
		$query  = preg_replace('/\?(.*)$/', '$1', $route);
		$route  = implode('/', array_slice($url, 0, -2)) . "/controllers/" . implode('/', array_splice($url, -2, -1));

		//Transforms the route from URL format to FileSystem format
		$namespace = CKernel::getInstance()->convertURLNamespaceToArbitrage($route);
		$this->requireController($namespace);

		//Create an instance of the controller
		$class = CKernel::getInstance()->convertArbitrageNamespaceToPHP($namespace) . "Controller";
		if(!class_exists($class))
			throw new EWebApplicationException("Controller '$class' does not exist.");

		//Get package
		$package = $this->getPackage(preg_replace('/\.Controller.*$/i', '', $namespace));

		//Get ajax controller if exists
		if($ajax)
		{
			//Require ajax controller
			$namespace = preg_replace('/\.Controllers\./i', '.Ajax.', $namespace);
			$this->requireController($namespace);

			//Get class
			$class = CKernel::getInstance()->convertArbitrageNamespaceToPHP($namespace) . "AjaxController";
			if(!class_exists($class))
				throw new EWebApplicationException("Ajax controller '$class' does not exist.");
		}

		//Create controller
		$controller                = $class::createController($this, (($package)? $package : $this));
		$this->_controller_queue[] = $controller;

		//Set action for controller
		$action = new CAction($controller, $action);
		$controller->setAction($action);

		return $controller;
	}

	/**
	 * Method requires the controller.
	 * @param string $namespace The arbitrage namespace where the controller resides.
	 * @throws \Framework\Exceptions\EWebApplicationException
	 */
	public function requireController($namespace)
	{
		//Get namespace
		$namespace = explode('.', $namespace);
		$count     = count($namespace);

		//Lowercase
		$namespace[$count-1] = preg_replace('/Controller$/i', '', $namespace[$count-1]);
		$namespace[$count-1] = strtolower($namespace[$count-1]);
		$namespace           = implode('.', $namespace);
		$ret = CKernel::getInstance()->requireFile($namespace, true, array('_application' => $this));

		if(!$ret)
			throw new EWebApplicationException("Unable to load controller '$namespace'.");
	}

	/**
	 * Method requires a renderable object.
	 * @param string $namespace The arbitrage namespace where the renderable object resides.
	 * @throws \Framework\Exceptions\EWebApplicationException
	 */
	public function requireRenderable($namespace)
	{
		//Require the file
		$variables = array('_application' => $this);
		CKernel::getInstance()->requireFile($namespace, true, $variables);
	}

	/**
	 * Method forwards execution to another controller specified by Arbitrage namespace.
	 * @param string $namespace The namespace of the Controller/Action to forward the execution to.
	 * @param array $opt_variables The variables to pass to the constructor.
	 * @param boolean $opt_render Render the returned results from the action.
	 * @param returns Returns the result from the action.
	 */
	public function forward($namespace, $opt_variables=array(), $opt_render=false)
	{
		static $request = array();

		//Take out action from namespace
		$action     = explode('.', $namespace);
		$controller = implode('.', array_slice($action, 0, -1));
		$action     = $action[count($action)-1];

		//Check if class exists
		$class = CKernel::getInstance()->convertArbitrageNamespaceToPHP($controller);
		if(!class_exists($class))
			throw new EWebApplicationException("Unable to forward to '$namespace' because it does not exist!");

		//Update opt_variables and set them into _REQUEST
		if(count($opt_variables))
		{
			$request[] = $_REQUEST;
			$_REQUEST = $opt_variables;
		}

		//Push variables
		$package    = $this->getPackage(preg_replace('/\.[^\.]+Controller\.[^\.]+$/', '', $namespace));
		$controller = $class::createController($this, $package);
		$this->_controller_queue[] = $controller;

		//Set action
		$action = new CAction($controller, $action);
		$controller->setAction($action);

		//Execute controller
		$ret = $controller->execute();

		if($opt_render)
			$controller->render($ret);

		//Pop variables
		array_pop($this->_controller_queue);
		if(count($request))
			$_REQUEST = array_pop($request);

		return $ret;
	}

	/**
	 * Method returns the current controller.
	 * @return \Framework\Base\CController Returns the current controller.
	 */
	public function getController()
	{
		return ((isset($this->_controller_queue[0]))? $this->_controller_queue[0] : NULL);
	}

	/**
	* Method converts an Arbitrage Namespace to a filesystem path locating the view directory.
	* @param string $namespace The namespace to convert.
	* @return Returns a filesystem path.
	*/
	public function getViewPathFromArbitrageNamespace($namespace)
	{
		//Get file
		$path = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPath($namespace);
		$path = explode('/', $path);
		$file = array_splice($path, -1);
		$file = $file[0];

		//insert into array
		array_splice($path, -1, 0, array('views'));
		return implode('/', $path) . "/$file";
	}


	/** Overloaded Error Handling Methods **/

	/**
	 * Method handles errors.
	 */
	public function handleError(\Framework\Interfaces\IEvent $event)
	{
		//TODO: Handle Debug Mode Exceptions

		$config = $this->getConfig();
		$debug  = ((isset($config->arbitrage2->debugMode))? $config->arbitrage2->debugMode : false);

		$this->handleException($event);
	}

	/**
	 * Method intializes services specified in the application configuration file.
	 */
	public function handleException(\Framework\Interfaces\IEvent $event)
	{
		//TODO: Handle HTTP Exceptions
		//TODO: Handle Debug Mode Exceptions
		$service = CKernel::getInstance()->getService($this, 'errorHandler');
		if($service !== NULL)
			$service->handleEvent($event);
		else
			$this->_printEvent($event);

		$event->stopPropagation();
		$event->preventDefault();
	}
	/** End Overloaded Error Handling Methods **/

	/**
	 * Method that initializes services.
	 */
	protected function _initializeServices()
	{
		//Ensure error_handler service is defined
		$services = $this->getConfig()->arbitrage2->services;
		if(!isset($services->errorHandler))
			$services->errorHandler = array('Framework.ErrorHandler.CErrorHandlerService' => array('debugMode' => $this->getConfig()->arbitrage2->debugMode));

		parent::_initializeServices();
	}

	/**
	 * Method prints out in HTML format the error or exception event.
	 * @param \Framework\Interfaces\IEvent $event The event to print out.
	 */
	private function _printEvent(\Framework\Interfaces\IEvent $event)
	{
		echo '<style type="text/css"> h3 { border-bottom: 1px solid black; } div.title { font-weight: bold; float: left; width: 100px; } div.value { float: left; } div.tracenumber { float: left; width: 100px; } div.tracefile { float: left; }</style>';
		echo '<h1>Arbitrage2: Global Exception Caught</h1><br />';
		echo '<h3>Exception</h3>';
		echo '<div class="title">Message:</div><div class="message">' . $event->message . '</div><div style="clear: both;"></div>';
		echo '<div class="title">Code:</div><div class="message">' . ((string) $event->code) . '</div><div style="clear: both;"></div>';
		echo '<div class="title">File:</div><div class="message">' . $event->file . '</div><div style="clear: both;"></div>';
		echo '<div class="title">Line:</div><div class="message">' . $event->line . '</div><div style="clear: both;"></div>';
		
		//Trace
		echo '<h3>Trace</h3>';
		$cnt = count($event->trace)-1;
		echo '<div class="title tracenumber">Trace</div><div class="title tracefile">File (line)</div><div style="clear: both;"></div>';
		for($i=0; $i<$cnt; $i++)
			echo '<div class="tracenumber">' . $i . '</div><div class="tracefile">' . $event->trace[$i]['file'] . ' (' . $event->trace[$i]['line'] . ')</div><div style="clear: both;"></div>';
	}
}
?>
