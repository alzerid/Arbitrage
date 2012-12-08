<?php
namespace Framework\Database2\Model;

class CDatabaseModel extends \Framework\Model\CMomentoModel
{
	static public $SERVICE;

	/* Method returns a query object for querying the database.
	 * @return \Framework\Database2\CDatabaseQuery Returns a database query.
	 */
	static public function query()
	{
		//TODO: Cache query??

		//Grab the properties and determine what todo
		$properties = static::properties();
		$driver     = ((isset($properties['connection']))? $properties['connection'] : '_default');
		$driver     = self::$SERVICE->getDriver($driver);
		$properties = array_merge($driver->getProperties(), $properties);
		$type       = ucwords($driver->getDriverType());

		//Unset properties
		unset($properties['connection']);

		//Create query object
		$query = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP("Framework.Database2.Drivers.$type.CQueryDriver");
		$query = new $query($driver, $properties['database'], $properties['table']);

		//Create CQuery Model from driver
		$model = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP("Framework.Database2.Drivers.$type.CQueryModel");
		$model = new $model($query, \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage(get_called_class()));

		return $model;
	}
	
	/**
	 * Method returnd default.
	 */
	static public function defaults()
	{
		throw new \Framework\Exceptions\EDatabaseException("Model must have defaults.");
	}

	/**
	 * Method returns the properties of the model.
	 * @return array Returns the array of properties.
	 */
	static public function properties()
	{
		return array();
	}

	/**
	 * Method creates, converts raw database data into a database model.
	 */
	static public function create(array $data=array())
	{
		//Create model
		$class = get_called_class();
		$model = new $class($data);

		//Merge
		$model->merge();

		//Convert data to specific types
		var_dump($class, $data);
		die(__METHOD__);

		return $model;
	}

	/** Model instance methods **/

	/**
	 * Method saves the model into the database.
	 */
	public function save()
	{
		//TODO: Merge
		die(__METHOD__);
	}

	/**
	 * Method inserts the model into the database.
	 */
	public function insert()
	{
		die(__METHOD__);
	}

	/**
	 * Method updates the data base entries from th emode.
	 */
	public function update()
	{
		die(__METHOD__);
	}
	/** End model instance methods **/

	/** 
	 * Method merges the variables array into the data array.
	 */
	public function merge()
	{
		die(__METHOD__);
	}

	/**
	 * Method returns the properties for this model.
	 * @return array Returns the properties.
	 */
	static private function _getProperties()
	{
		//TODO: Add to static variable here to cache properties

		//Get model properties
		$properties = static::properties();
		$driver     = ((isset($properties['config']))? $properties['config'] : '_default');
		$driver     = self::$SERVICE->getDriver($driver);

		//Now get native model properties (native to db)

		var_dump($properties);
		die(__METHOD__);

		return $properties;
	}
}
?>
