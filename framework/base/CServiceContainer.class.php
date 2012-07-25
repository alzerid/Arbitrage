<?
namespace Arbitrage2\Base;
use \Arbitrage2\Utils\CArrayObject;
use \Arbitrage2\Exceptions\EArbitrageServiceException;

class CServiceContainer
{
	private $_service_type;    //Service name
	private $_services;        //List of services associated with the application

	/**
	 * Constructor that builds the CServiceContainer object.
	 * @param string $service The service name that this container will manage.
	 */
	public function __construct($service_type)
	{
		$this->_service_type = $service_type;
		$this->_services     = array();
	}

	/**
	 * Method returns the service type associated with this container.
	 * @return string Returns the service type.
	 */
	public function getServiceType()
	{
		return $this->_service_type;
	}

	/**
	 * Method returnst he service associated with the application.
	 * @param \Arbitrage2\Base\CApplication $application The application the service is registered to.
	 * @return \Arbitrage2\Base\CService Returns the appropriate service or NULL.
	 */
	public function getService(\Arbitrage2\Base\CApplication $application)
	{
		foreach($this->_services as $service)
		{
			if($service->application == $application)
				return $service->service;
		}

		return NULL;
	}

	/**
	 * Registers an application to this service.
	 * @param \Arbitrage2\Base\CApplication $application The application to register
	 * @param string $path The filesystem path of the service.
	 * @param string $class The service object to use.
	 * @param \Arbitrage2\Config\CArbitrageConfig $config The config to use for this service and application combination.
	 * @return boolean Returns false if application already registered or true.
	 */
	public function registerApplicationToService(\Arbitrage2\Base\CApplication $application, $path, $class, $config)
	{
		//Find if applicatoin already registered
		foreach($this->_services as $obj)
		{
			if($obj->application == $application)
				return false;
		}

		//Create object
		$obj = new CArrayObject;
		$obj->application = $application;
		$obj->service     = new $class($application, $path, $config);
		$obj->service->initialize();

		//Ensure service is of the same type
		if($this->getServiceType() != $obj->service->getServiceType())
			throw new EArbitrageServiceException("Service requested '{$this->getServiceType()}' but got service type '{$obj->service->getServiceType()}'.");

		//Add to _services
		$this->_services[] = $obj;

		return true;
	}

	public function unregisterApplicationFromService($application)
	{
		foreach($this->_services as $key=>$app)
		{
			if($application == $app)
			{
				unset($this->_services[$key]);
				$this->_services = array_values($this->_services);
			}
		}
	}
}
?>
