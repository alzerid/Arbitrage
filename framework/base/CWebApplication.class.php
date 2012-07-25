<?
namespace Arbitrage2\Base;
use \Arbitrage2\Exceptions\EWebApplicationException;

class CWebApplication extends CApplication
{
	private $_renderable_paths;    //Paths where the renderables exist
	private $_controller_queue;    //Controller queue
	private $_packages;            //A list of packages
	private $_router;              //Router instance

	/**
	 * Initialize the Web Application.
	 */
	public function __construct()
	{
		parent::__construct($this);
		$this->_renderable_paths = array();
		$this->_controller_queue = array();
		$this->_packages         = array();
		$this->_router           = NULL;
	}

	/**
	 * Initializes the arbitrage application, loads the application config.
	 * @param string $path The path where the application resides in.
	 * @param string $namespace The namespace associated with the object.
	 */
	public function initialize($path, $namespace)
	{
		//Call parent
		parent::initialize($path, $namespace);

		//Require framework files
		CKernel::getInstance()->requireFrameworkFile("Base.CController");
		CKernel::getInstance()->requireFrameworkFile("Base.CAction");
		CKernel::getInstance()->requireFrameworkFile('Base.CRouter');
		CKernel::getInstance()->requireFrameworkFile('Base.CFilterChain');
		CKernel::getInstance()->requireFrameworkFile('Utils.CFlashPropertyObject');

		//Create relavent services
		$this->_initializeServices();

		//Create router instance
		$this->_router = new CRouter($this->getConfig()->webApplication->routes);
	}

	/** 
	 * Method runs the Web Application
	 */
	public function run()
	{
		//Get route
		$route = $this->_router->route($_SERVER['REQUEST_URI']);

		//Load the controller
		$controller = $this->loadController($route, isset($_REQUEST['_ajax']));

		//Execute the action
		$ret = $controller->execute();
		die('run CWeb');
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
		$url    = explode('/', $route);
		$action = preg_replace('/\?.*$/', '', $url[count($url)-1]);
		$route  = implode('/', array_slice($url, 0, 1)) . "/controllers/" . implode('/', array_slice($url, 1, count($url)-2));

		//Transforms the route from URL format to FileSystem format
		$namespace = CKernel::getInstance()->convertURLNamespaceToArbitrage($route);
		$this->requireController($namespace);

		//Create an instance of the controller
		$class = CKernel::getInstance()->convertArbitrageNamespaceToPHP($namespace) . "Controller";
		if(!class_exists($class))
			throw new EWebApplicationException("Controller '$class' does not exists.");

		//Create controller
		$controller                = $class::createController($this);
		$this->_controller_queue[] = $controller;

		//Set action for controller
		$action = new CAction($controller, $action);
		$controller->setAction($action);

		return $controller;
	}

	/**
	 * Method requires the controller.
	 * @param string $namespace The arbitrage namespace where the controller resides.
	 * @throws \Arbitrage2\Exceptions\EWebApplicationException
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
	 * @throws \Arbitrage2\Exceptions\EWebApplicationException
	 */
	public function requireRenderable($namespace)
	{
		//Require the file
		$variables = array('_application' => $this);
		if(preg_match('/^Arbitrage2\./', $namespace))
			CKernel::getInstance()->requireFrameworkFile(preg_replace('/^Arbitrage2\./', 'Framework.', $namespace), true, $variables);
		else
			CKernel::getInstance()->requireFile($namespace, true, $variables);
	}

	/**
	 * Method forwards execution to another controller specified by Arbitrage namespace.
	 * @param string $namespace The namespace of the Controller/Action to forward the execution to.
	 * @param array $opt_variables The variables to pass to the constructor.
	 */
	public function forward($namespace, $opt_variables)
	{
		static $request = array();

		//Take out action from namespace
		$action     = explode('.', $namespace);
		$controller = implode('.', array_slice($action, 0, -1));
		$action     = $action[count($action)-1];

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
		$controller = $class::createController($this);
		$this->_controller_queue[] = $controller;

		//Set action
		$action = new CAction($controller, $action);
		$controller->setAction($action);

		//Execute controller and return raw results
		$controller->execute();

		//Pop variables
		array_pop($this->_controller_queue);
		if(count($request))
			$_REQUEST = array_pop($request);
	}

	/** Overloaded Error Handling Methods **/

	/**
	 * Method handles errors.
	 */
	public function handleError(\Arbitrage2\Interfaces\IEvent $event)
	{
		//TODO: Handle Debug Mode Exceptions

		$config = $this->getConfig();
		$debug  = ((isset($config->arbitrage2->debugMode))? $config->arbitrage2->debugMode : false);

		$this->handleException($event);

		return;

		if($debug === true)
		{
			//$this->_forwardError($event);
			//Grab error handler controller
			//TODO: Render error
			var_dump($event);
			die("ERROR");
		}
		elseif($event->exception instanceof \Arbitrage2\Exceptions\EHTTPException)
		{
			die("HTTP Exception!");
		}
		else
		{
			//Show http_500
			die("show http500");
		}

		$event->stopPropagation();
		$event->preventDefault();
	}

	/**
	 * Method intializes services specified in the application configuration file.
	 */
	public function handleException(\Arbitrage2\Interfaces\IEvent $event)
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
			$services->errorHandler = array('Arbitrage2.ErrorHandler.CErrorHandlerService' => array('debugMode' => $this->getConfig()->arbitrage2->debugMode));

		//Call parent initialize services
		CKernel::getInstance()->initializeServices($this);
	}

	/**
	 * Method prints out in HTML format the error or exception event.
	 * @param \Arbitrage2\Interfaces\IEvent $event The event to print out.
	 */
	private function _printEvent(\Arbitrage2\Interfaces\IEvent $event)
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
