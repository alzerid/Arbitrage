<?php
namespace Framework\Database2\Model;

//TODO: Save typecasts

class CDatabaseModel extends \Framework\Model\CMomentoModel
{
	static public $SERVICE;

	/**
	 * Method constructs the model.
	 */
	public function __construct($data=array())
	{
		//Get properties of model
		$properties = $this->_getProperties();
		$defaults   = static::defaults();

		//Set id from _idKey
		if(!isset($defaults[$properties['idKey']]))
			$defaults[$properties['idKey']] = new \Framework\Database2\Model\DataTypes\CDatabaseID;
		elseif(!($defaults[$properties['idKey']] instanceof \Framework\Database2\Model\DataTypes\CDatabaseID))
			throw new \Framework\Exceptions\EDatabaseDriverException("ID Key '{$data[$properties['idKey']]}' is not a CDatabaseID DataType.");

		//Set defaults
		\Framework\Model\CModel::__construct($defaults);

		//Ensure variables is in defaults
		$this->_setVariables($data);
		die(__METHOD__);
		$this->merge();
	}

	/**
	 * Method returns a query object for querying the database.
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
		throw new \Framework\Exceptions\EDatabaseDriverException("Model must have defaults.");
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
		var_dump($class);
		die(__METHOD__);

		//Merge
		$model->merge();

		//Convert data to specific types
		var_dump($model);
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
		//TODO: Handle structure classes

		//Recursively merge
		foreach($this->_variables as $key=>$val)
		{
			var_dump($key);
			die(__METHOD__);
		}

		die(__METHOD__);
	}

	/**
	 * Method overrides the set magic method.
	 * @param $name The variable name to set.
	 * @param $val The value to set to.
	 */
	protected function _setData($name, $val)
	{
		//Check if $name exists in $this->_data
		$class = get_called_class();
		if(!array_key_exists($name, $this->_data))
			throw new \Framework\Exceptions\EDatabaseDriverException("Variable '$name' not in model '$class'.");

		//TODO: If DataType Object ensure the same DataType Object
		//TODO: Ensure same type

		//Set variables
		$this->_variables[$name] = $val;
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
		$type  = ucwords($driver->getDrivertype());
		$class = "\\Framework\\Database2\\Drivers\\{$type}\\CDatabaseModel";
		
		//Merge properties
		$properties = array_merge($class::properties(), $properties);

		return $properties;
	}

	/**
	 * Method sets the variable array.
	 * @param $variables The variables array.
	 */
	private function _setVariables($data)
	{
		//TODO: If type is Structure, call _setVariable
		foreach($data as $key=>$val)
			$this->$key = $val;
	}
}
?>
