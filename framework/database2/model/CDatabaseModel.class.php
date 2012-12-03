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

		return new \Framework\Database2\Model\CQueryModel($query, \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage(get_called_class()));
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
	static public function create(array $data)
	{
		//Create model
		$class = get_called_class();
		$model = new $class;

		//Convert data to specific types
		var_dump($class, $data);
		die(__METHOD__);
	}

	/** Model instance methods **/
	public function save()
	{
		die(__METHOD__);
	}

	public function insert()
	{
		die(__METHOD__);
	}

	public function update()
	{
		die(__METHOD__);
	}
	/** End model instance methods **/

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
