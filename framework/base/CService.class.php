<?
namespace Framework\Base;
use \Framework\Exceptions\EArbitrageServiceException;

abstract class CService
{
	protected $_config;         //The config object related to the service
	private $_application;    //The application this service is tied to
	private $_service_type;   //Service type
	private $_namespace;      //The namespace of the service
	private $_fs_path;        //Filesystem path

	/**
	 * Constructor for \Framework\Base\CService.
	 * @param \Framework\Base\CApplication $application The application this service is tied to.
	 * @param string $type The type of service being instantiated.
	 * @param string $path The path of where the service is located on the File System.
	 * @param \Framework\Config\CArbitrageConfig $config The configuration associated with the service.
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

	/**
	 * Method returns the Service type.
	 * @return Returns the service type.
	 */
	public function getServiceType()
	{
		return $this->_service_type;
	}

	/**
	 * Method returns the namespace of the service.
	 * @return Returns the namespace of the service.
	 */
	public function getNamespace()
	{
		return $this->_namespace;
	}

	/**
	 * Method returns the configuration object.
	 * @return Returns the configuration object for this service.
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * Method returns the application this service is tied to.
	 * @return \Framework\Base\CApplication The application returned.
	 */
	public function getApplication()
	{
		return $this->_application;
	}

	/**
	 * Method requires a service file.
	 * @param string $namespace The namespace the rqeuire.
	 * @param $opt_variables Optional parameter that pases variables into the required file.
	 * @throws \Framework\Exceptions\EArbitrageServiceException
	 */
	public function requireServiceFile($namespace, $opt_variables=array())
	{
		$path = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPath($namespace);
		$path = $this->_fs_path . "/{$path}.class.php";
		if(!file_exists($path))
			throw new EArbitrageServiceException("Unable to require service file '$namespace' ($path)");

		//Extract opt_variables
		if(count($opt_variables))
			extract($opt_variables);

		//Require the file
		require_once($path);
	}

}
?>
