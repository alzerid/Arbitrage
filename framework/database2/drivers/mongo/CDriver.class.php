<?php
namespace Framework\Database2\Drivers\Mongo;

class CDriver extends \Framework\Database2\Drivers\CDriver
{
	/**
	 * Method constructs the Mongo Driver
	 */
	public function __construct($host="127.0.0.1", $port=27017)
	{
		//Assign variables
		$this->_driver_type = 'mongo';
		$this->_host        = $host;
		$this->_port        = $port;

		//Create handle and connect
		$uri = "mongodb://{$this->_host}:{$this->_port}";
		$this->_handle = new \Mongo($uri);
	}

	/**
	 * Method connects to the database.
	 */
	public function connect()
	{
		$this->_handle->connect();
	}

	/**
	 * Method closes the database connection.
	 */
	public function close()
	{
		$this->_handle->close();
	}
}
?>
