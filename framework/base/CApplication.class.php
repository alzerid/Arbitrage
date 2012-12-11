<?
namespace Framework\Base;

abstract class CApplication extends CPackage implements \Framework\Interfaces\IErrorHandlerListener, \Framework\Interfaces\IAutoLoadObserver
{
	private $_packages;             //A list of packages registered to this application
	private $_auto_load_listeners;  //List of auto load handlers

	/**
	 * Initializes the arbitrage application, loads the application config.
	 */
	public function initialize()
	{
		//Set arrays
		$this->_packages            = array();
		$this->_auto_load_listeners = array();

		//Setup error handler
		\Framework\ErrorHandler\CErrorHandlerObserver::getInstance()->addListener($this);

		//Register autoload
		spl_autoload_register(array($this, 'handleAutoLoad'), true, true);

		//Load model items
		CKernel::getInstance()->requireFrameworkFile('Model.CModelIterator');
		CKernel::getInstance()->requireFrameworkFile('Model.CModel');
		CKernel::getInstance()->requireFrameworkFile('Model.CMomentoModel');
		CKernel::getInstance()->requireFrameworkFile('Model.DataTypes.CDateDataType');
		CKernel::getInstance()->requireFrameworkFile('Model.Structures.CArrayStructure');

		//Call CPackage::initialize
		parent::initialize();
	}

	/**
	 * Method returns a package based on namespace.
	 * @param string $namespace The namespace to retrieve.
	 * @return \Arbitrage\Base\CPackage The returned package.
	 */
	public function getPackage($namespace)
	{
		$key = strtolower($namespace);
		return ((isset($this->_packages[$key]))? $this->_packages[$key] : NULL);
	}

	/**
	 * Method returns the service requested.
	 * @param string $service The service to retreive.
	 * @return \Framework\Base\CService Returns the service.
	 */
	public function getService($service)
	{
		return CKernel::getInstance()->getService($this, $service);
	}

	/**
	 * Abstract method that all applications must contain. This is called
	 * upon application execution.
	 */
	abstract public function run();

	/**
	 * Method that initializes services.
	 */
	protected function _initializeServices()
	{
		//Call parent initialize services
		CKernel::getInstance()->initializeServices($this);
	}

	/**
	 * Method initializes packages.
	 */
	protected function _initializePackages()
	{
		//Get arbitrage2 config
		$config = $this->getConfig();

		//Check if arbitrage2 is defined
		if($config->arbitrage2->packagePaths)
		{
			//Add package paths
			if($config->arbitrage2->packagePaths)
			{
				foreach($config->arbitrage2->packagePaths as $path)
					CKernel::getInstance()->registerPackagePath($path);
			}

			//Include the packages
			if($config->arbitrage2->packages)
			{
				//Grab packages
				$packages = $config->arbitrage2->packages;

				//Create package
				foreach($packages as $package => $lconfig)
					$this->_packages[strtolower($package)] = CKernel::getInstance()->createPackage($package, $this, $packages[$package]);
			}
		}
	}

	/**
	 * Method is called to register auto load handlers.
	 * @param \Framework\Interfaces\IAutoLoadListener $listener The listener to register.
	 */
	public function registerAutoLoadListener(\Framework\Interfaces\IAutoLoadListener $listener)
	{
		$this->_auto_load_listeners[] = $listener;
	}

	/**
	 * Method is called when auto load is triggered.
	 * @param string $class The class to attempt to load.
	 */
	public function handleAutoLoad($class)
	{
		//Create new event
		$event = new \Framework\Events\CAutoLoadEvent($this, $class);

		//Trigger each listener
		foreach($this->_auto_load_listeners as $listener)
		{
			$listener->handleAutoLoad($event);
			if(!$event->getPropagation())
				break;
		}
	}

	/**
	 * Method handles errors.
	 */
	public function handleError(\Framework\Interfaces\IEvent $event)
	{
		die('CApplication::handleError');
	}
	
	/**
	 * Methods handles exceptions.
	 */
	public function handleException(\Framework\Interfaces\IEvent $event)
	{
		die('CApplication::handleException');
	}
}
?>
