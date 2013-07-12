<?php
namespace Framework\Database2\Drivers\Mongo;

class CDriver extends \Framework\Database2\Drivers\CDriver
{
	/**
	 * Method constructs the Mongo Driver
	 */
	public function __construct(array $properties=array())
	{
		//Call parent
		parent::__construct($properties);
		$this->_properties['port'] = 27017;

		//Assign variables
		$this->_driver_type = 'mongo';

		//Create handle and connect
		$uri = "mongodb://{$this->_properties['host']}:{$this->_properties['port']}";
		$this->_handle = ((class_exists("\\MongoClient"))? new \MongoClient($uri) : new \Mongo($uri));
	}

	/**
	 * Method returns the properties.
	 */
	public function getProperties()
	{
		return $this->_properties;
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
