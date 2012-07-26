<?
namespace Framework\Base;

abstract class CApplication extends CPackage implements \Arbitrage2\Interfaces\IErrorHandlerListener
{
	private $_packages;            //A list of packages registered to this application

	/**
	 * Initializes the arbitrage application, loads the application config.
	 */
	public function initialize()
	{
		//Set arrays
		$this->_packages = array();

		//Setup error handler
		\Arbitrage2\ErrorHandler\CErrorHandlerObserver::getInstance()->addListener($this);

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
		if($config->arbitrage2 && $config->arbitrage2->packages)
		{
			//Grab packages
			$packages = $config->arbitrage2->packages;

			//Create package
			foreach($packages as $package => $lconfig)
			{
				$key = preg_replace('/\.[^\.]+$/', '', strtolower($package));
				$this->_packages[$key] = CKernel::getInstance()->createPackage($package, $this, new \Arbitrage2\Config\CArbitrageConfigProperty($lconfig));
			}
		}
	}

	/**
	 * Method handles errors.
	 */
	public function handleError(\Arbitrage2\Interfaces\IEvent $event)
	{
		die('CApplication::handleError');
	}
	
	/**
	 * Methods handles exceptions.
	 */
	public function handleException(\Arbitrage2\Interfaces\IEvent $event)
	{
		die('CApplication::handleException');
	}
}
?>
