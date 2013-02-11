<?
namespace Framework\Base;

abstract class CCLIApplication extends CApplication
{
	protected $_description;

	/**
	 * Initializes the arbitrage application, loads the application config.
	 * @param string $path The path where the application resides in.
	 * @param string $namespace The namespace associated with the object.
	 */
	public function initialize()
	{
		global $argv;

		//Call parent
		parent::initialize();
		$this->_description = "UNKNOWN DESCRIPTION";

		//Create relavent services
		$this->_initializeServices();

		//Initialize Packages
		//$this->_initializePackages();
	}

	/**
	 * Abstract method that prints the help menu.
	 */
	abstract public function help();

	/**
	 * Method returns the description of this CLI Application.
	 * @return string Return the description of this CLI Application.
	 */
	public function getDescription()
	{
		return $this->_description;
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
	 * Method overloads the CPackage _getConfigurationPath.
	 * @return Returns the path to the configuration file.
	 */
	protected function _getConfigurationPath()
	{
		$path = CKernel::getInstance()->convertArbitrageNamespaceToPath($this->getNamespace() . ".config");
		$file = $this->getPath() . "/" . preg_replace('/([^\/]*)\/.*(\/[^\/]*)$/', '\1\2', $path) . "/config.php";

		return $file;
	}

	/**
	 * Method prints out in HTML format the error or exception event.
	 * @param \Framework\Interfaces\IEvent $event The event to print out.
	 */
	private function _printEvent(\Framework\Interfaces\IEvent $event)
	{
		printf("Arbitrage: Global Exception Caught\n");
		printf("%s\n", str_repeat("=", 30));
		printf("Message: %s\n", $event->message);
		printf("Code: %d\n", $event->code);
		printf("File: %s\n", $event->file);
		printf("Line: %s\n\n", $event->line);
		
		//Trace
		printf("Trace\n");
		printf("%s\n", str_repeat('=', 30));
		$cnt = count($event->trace)-1;
		for($i=0; $i<$cnt; $i++)
		{
			printf("Trace #: %d\n", $i);
			printf("File: %s\n", $event->trace[$i]['file']);
			printf("Line #: %d\n\n", $event->trace[$i]['line']);
		}
	}
}
?>
