<?
namespace Framework\Base;
use \Arbitrage2\Exceptions\EArbitrageServiceException;

abstract class CService
{
	private $_application;    //The application this service is tied to
	private $_service_type;   //Service type
	private $_namespace;      //The namespace of the service
	private $_config;         //The config object related to the service
	private $_fs_path;        //Filesystem path

	/**
	 * Constructor for \Arbitrage2\Base\CService.
	 * @param \Arbitrage2\Base\CApplication $application The application this service is tied to.
	 * @param string $type The type of service being instantiated.
	 * @param string $path The path of where the service is located on the File System.
	 * @param \Arbitrage2\Config\CArbitrageConfig $config The configuration associated with the service.
	 */
	public function __construct($application, $path, $config)
	{
		$this->_application  = $application;
		$this->_service_type = static::$_SERVICE_TYPE;
		$this->_namespace    = CKernel::getInstance()->convertPHPNamespaceToArbitrage(get_class($this));
		$this->_config       = $config;
		$this->_fs_path      = $path;
	}

	/**
	 * Abstract method that is run for initializing services.
	 */
	abstract public function initialize();

	public function getServiceType()
	{
		return $this->_service_type;
	}

	public function getNamespace()
	{
		return $this->_namespace;
	}

	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * Method returns the application this service is tied to.
	 * @return \Arbitrage2\Base\CApplication The application returned.
	 */
	public function getApplication()
	{
		return $this->_application;
	}

	public function requireServiceFile($namespace)
	{
		$path = $this->_fs_path . "/{$namespace}.class.php";
		if(!file_exists($path))
			throw new EArbitrageServiceException("Unable to require service file '$namespace' ($path)");

		//Require the file
		require_once($path);
	}

}
?>
