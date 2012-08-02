<?
namespace Framework\Database;
use \Framework\Base\CService;

class CDatabaseService extends CService implements \Framework\Interfaces\IAutoLoadListener
{
	static protected $_SERVICE_TYPE = "database";   //Service type
	protected $_db_config;                          //Database configuration
	protected $_drivers;                            //Driver list

	/**
	 * Method initializes the service.
	 */
	public function initialize()
	{
		//Require base service classes
		$this->requireServiceFile("EModelException");                      //Model Exception classes
		$this->requireServiceFile("CDatabaseDriver");                      //Database driver base class
		$this->requireServiceFile("CModelResults");                        //Wrapper class that keeps the results
		$this->requireServiceFile("Types.CModelData");                     //Basic Model data type
		$this->requireServiceFile("Types.CModelArrayData");                //Array data type
		$this->requireServiceFile("Types.CModelHashData");                 //Hash (key value pair) type
		$this->requireServiceFile("CDriverQuery");                         //Driver query
		$this->requireServiceFile("CDriverBatch");                         //Driver batch
		$this->requireServiceFile("CModel", array('_service' => $this));   //Model

		//Load necessary drivers
		$this->_db_config = array();
		$this->_drivers   = array();
		$config           = $this->getConfig();
		$loaded           = array();
		foreach($config as $key => $val)
		{
			//Load driver if not loaded
			if(!in_array($val['driver'], $loaded))
			{
				//Load the driver
				$namespace = "Drivers." . ucwords($val['driver']);
				$this->requireServiceFile("$namespace.CDatabaseDriver");
				$this->requireServiceFile("$namespace.CDriverQuery");
				//$this->requireServiceFile("$namespace.CDriverBatch"); //TODO: Add back batch
				$this->requireServiceFile("$namespace.CModelResults");

				//Add to loaded
				$loaded[] = $val['driver'];
			}
		}

		//TODO: Register this service to the application autoload events
		$this->getApplication()->registerAutoLoadListener($this);
	}

	/**
	 * Method returns a driver for DB access functionality.
	 * @param array $opt_prop The properties to merge with.
	 * @returns array The resulting driver.
	 */
	public function getDriver($opt_prop=array())
	{
		$key    = ((isset($opt_prop['config']))? $opt_prop['config'] : '_default');
		$config = $this->getConfig();

		if(!isset($config[$key]))
			throw new EDatabaseDriverException("Unable to load config for '$key'.");

		//Merge properties
		$config = array_merge($config[$key]->toArray(), $opt_prop);
		$driver = $config['driver'];
		$key    = "$driver.{$config['host']}.{$config['port']}";

		//Get driver
		if(isset($this->_drivers[$key]))
			return $this->_drivers[$key];

		//Create driver
		$class                = '\\Framework\\Database\\Drivers\\' . ucwords($config['driver']) . '\\CDatabaseDriver';
		$this->_drivers[$key] = new $class($config);

		return $this->_drivers[$key];
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
				throw new EDatabaseDriverException("Unable to load model '$namespace'.");

			//Loaded, prevent from propagating
			$event->stopPropagation();
		}
	}
}
?>
