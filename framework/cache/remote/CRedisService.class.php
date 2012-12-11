<?
namespace Framework\Cache\Remote;

class CRedisService extends \Framework\Base\CService
{
	static protected $_SERVICE_TYPE = "redis";  //Service type
	protected $drivers;                         //Driver list

	/**
	 * Method initializes the service.
	 */
	public function initialize()
	{
		//Require Driver
		$this->requireServiceFile('CRedisDriver');
		$this->_drivers = array();
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

		//Check if driver already exists
		if(!isset($this->_drivers[$key]))
			$this->_drivers[$key] = new CRedisDriver($config->$key);

		return $this->_drivers[$key];
	}
}
?>
