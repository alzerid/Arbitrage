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
		$this->requireServiceFile('Drivers.CDriver');
		$this->requireServiceFile('Drivers.CQueryDriver');
		$this->requireServiceFile('Model.CModel');
		$this->requireServiceFile('Model.CDatabaseModel');
		$this->requireServiceFile('Model.CQueryModel');
		$this->requireServiceFile('Model.CCollectionModel');
		$this->requireServiceFile('Model.Structures.CStructure');
		$this->requireServiceFile('Model.Structures.CArray');
		//$this->requireServiceFile('Model.Structures.CHash');

		//Include all selectors
		$this->requireServiceFile('Selectors.CSelector');
		$this->requireServiceFile('Selectors.CArraySelector');

		//Set $SERVICE static variable
		\Framework\Database2\Model\CModel::$SERVICE = $this;

		//TODO: Load up all drivers
		$config = $this->getConfig();
		foreach($config as $name=>$properties)
		{
			$driver = ucwords($properties['driver']);

			//Require driver specific files
			$this->requireServiceFile("Drivers.$driver.CDatabaseModel");
			$this->requireServiceFile("Drivers.$driver.CDriver");
			$this->requireServiceFile("Drivers.$driver.CQueryDriver");
			$this->requireServiceFile("Drivers.$driver.CQueryModel");
			$this->requireServiceFile("Drivers.$driver.CCollectionModel");

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
		//TODO: Ensure driver exists as a class

		//Get driver
		$driver = ucwords($properties['driver']);
		$driver = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP("Framework.Database2.Drivers.$driver.CDriver");

		//Create new driver
		return new $driver($properties->toArray());
	}

	/**
	 * Method returns a driver for DB access functionality.
	 * @param array $opt_prop The properties to merge with.
	 * @returns array The resulting driver.
	 */
	public function getDriver($driver)
	{
		return ((isset($this->_drivers[$driver]))? $this->_drivers[$driver] : NULL);
	}

	/**
	 * Method listens for autoload events.
	 * @param \Framework\Interfaces\IEvent $event The event object containing event information.
	 */
	public function handleAutoLoad(\Framework\Interfaces\IEvent $event)
	{
		static $loaded = array();

		//Model autoload
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
		elseif(preg_match('/\\\Model\\\DataTypes/i', $event->class)) //DataType autoload
		{
			$class = "\\{$event->class}";
			if(isset($loaded[$class]))
				return;

			//Require the file
			$namespace = \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($class);
			$ret       = \Framework\Base\CKernel::getInstance()->requireFile($namespace, false);

			//Throw an expcetion if we are unable to load the DataType
			if(!$ret)
				throw new \Framework\Exceptions\EDatabaseDriverException("Unable to load data type model '$namespace'.");

			//Loaded, prevent from propagating
			$event->stopPropagation();
		}
	}
}
?>
