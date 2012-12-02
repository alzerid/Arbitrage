<?php
namespace Framework\Database2\Drivers;

abstract class CDriver
{
	protected $_driver_type;
	protected $_handle;
	protected $_host;
	protected $_port;

	/**
	 * Method returns a the driver type.
	 * @return Returns the port.
	 */
	public function getDriverType()
	{
		return $this->_driver_type;
	}

	/** 
	 * Method returns the handle.
	 * @return Returns the handle to the db.
	 */
	public function getHandle()
	{
		return $this->_handle;
	}

	/**
	 * Method returns the host this driver is connected to.
	 * @return string The host the driver is connected to.
	 */
	public function getHost()
	{
		return $this->_host;
	}

	/**
	 * Method returns the port the driver is connected to.
	 * @return int Returns the port.
	 */
	public function getPort()
	{
		return $this->_port;
	}

	/**
	 * Method returns the query driver.
	 * @param $database The database query.
	 * @param $table The table to query.
	 */
	public function getQuery($database, $table)
	{
		$type  = ucwords($this->_driver_type);
		$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP("Framework.Database2.Drivers.$type.CQueryDriver");
		$class = new $class($this, $database, $table);

		return $class;
	}

	/**
	 * Method connects to a database.
	 */
	abstract public function connect();

	/**
	 * Method closes the conection to the database.
	 */
	abstract public function close();
}
?>
