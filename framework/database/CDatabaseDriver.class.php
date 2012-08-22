<?
namespace Framework\Database;
abstract class CDatabaseDriver implements \Framework\Interfaces\IDriver
{
	protected $_handle;
	protected $_config;

	/**
	 * Class holds the handle and other information for the driver connection.
	 * @param $config The config to use for connection purposes.
	 */
	public function __construct($config)
	{
		$this->_config = $config;
	}

	/**
	 * Method returns the raw handle of the driver.
	 * @return Returns the handle.
	 */
	public function getHandle()
	{
		return $this->_handle;
	}

	/**
	 * Method retuns the configuration of this driver.
	 * @returns array Returns driver configuration.
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/*
	 * Abstract method retuns the form object.
	 */
	abstract public function getForm(array $form);

	/**
	 * Abstract method returns the correct Query class.
	 */
	abstract public function getQuery($class);

	/**
	 * Abstract method reutns a batch object.
	 */
	abstract public function getBatch();
}
?>
