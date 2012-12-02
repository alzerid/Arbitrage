<?
namespace Framework\Database2;

class CDatabaseService extends \Framework\Base\CService implements \Framework\Interfaces\IAutoLoadListener
{
	static protected $_SERVICE_TYPE = "database";   //Service type
	protected $_drivers;                            //Driver list

	/**
	 * Method initializes the service.
	 */
	public function initialize()
	{
		//TODO: Load up all model classes
		$this->requireServiceFile('Model.CDatabaseModel');
		$this->requireServiceFile('Drivers.CDriver');
		$this->requireServiceFile('Drivers.CQueryDriver');

		//Include all selectors
		$this->requireServiceFile('Selectors.CSelector');
		$this->requireServiceFile('Selectors.CArraySelector');

		//Set $SERVICE static variable
		\Framework\Database2\Model\CDatabaseModel::$SERVICE = $this;

		//TODO: Load up all drivers
		$config = $this->getConfig();
		foreach($config as $name=>$properties)
		{
			$driver = ucwords($properties['driver']);

			//Require driver specific files
			$this->requireServiceFile("Drivers.$driver.CDatabaseModel");
			$this->requireServiceFile("Drivers.$driver.CDriver");
			$this->requireServiceFile("Drivers.$driver.CQueryDriver");


			//Get or create driver
			$this->_drivers[$name] = $this->createDriver($properties);
		}

		//Register this service to the application autoload events
		$this->getApplication()->registerAutoLoadListener($this);
	}

	/**
	 * Method returns a driver for DB access.
	 * @param array $properties The properties for this database.
	 * @return array The resulting driver.
	 */
	public function createDriver($properties)
	{
		$driver = $properties['driver'];

		//TODO: Create the Driver and the CDriver class
	}

	/**
	 * Method returns a driver for DB access functionality.
	 * @param array $opt_prop The properties to merge with.
	 * @returns array The resulting driver.
	 */
	public function getDriver($driver)
	{
		die(__METHOD__);
		//return $this->
	}

	/**
	 * Method listens for autoload events.
	 * @param \Framework\Interfaces\IEvent $event The event object containing event information.
	 */
	public function handleAutoLoad(\Framework\Interfaces\IEvent $event)
	{
		static $loaded = array();

		if(preg_match('/Model$/', $event->class))
		{
			//Check if we already loaded
			$class = "\\{$event->class}";
			if(isset($loaded[$class]))
				return;

			//Require the file
			$namespace = explode('.', \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage(preg_replace('/Model$/', '', $class)));
			$namespace = implode('.', array_slice($namespace, 0, -1)) . "." . strtolower($namespace[count($namespace)-1]);
			$ret       = \Framework\Base\CKernel::getInstance()->requireFile($namespace, false);

			//Throw exception if unable to load model
			if(!$ret)
				throw new \Framework\Exceptions\EDatabaseDriverException("Unable to load model '$namespace'.");

			//Loaded, prevent from propagating
			$event->stopPropagation();
		}
	}
}
?>
