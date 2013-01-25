<?php
namespace Framework\Database2\Model;

class CModel extends \Framework\Model\CMomentoModel
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

		//Set defaults
		\Framework\Model\CModel::__construct($defaults);

		//Ensure variables is in defaults
		$this->_setVariables($data);
	}

	/**
	 * Method returns default values of the model.
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
	 * Method merges the variables array into the data array.
	 */
	public function merge()
	{
		//TODO: Handle structure classes
		//TODO: Handle DataTypes???

		//Recursively merge
		if($this->_variables)
		{
			foreach($this->_variables as $key=>$val)
			{
				if($val instanceof \Framework\Database2\Model\Structures\CArray)
					$val->merge();
				else
					$this->_data[$key] = $val;
			}
		}

		//Reset _variables
		$this->_variables = array();
	}

	/**
	 * Method returns the properties for this model.
	 * @return array Returns the properties.
	 */
	static protected function _getProperties()
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
	 * Method overrides the set magic method.
	 * @param $name The variable name to set.
	 * @param $val The value to set to.
	 */
	protected function _setData($name, $val)
	{
		//Check if $name exists in $this->_data
		if(!array_key_exists($name, $this->_data))
		{
			$class = get_called_class();
			throw new \Framework\Exceptions\EDatabaseDriverException("Variable '$name' not in model '$class'.");
		}

		//TODO: If DataType Object ensure the same DataType Object
		//TODO: Ensure same type
		if($this->_data[$name] instanceof \Framework\Database2\Model\Structures\CArray)
			$this->_data[$name]->set($val);
		else
			$this->_variables[$name] = $val;  //Set variables
	}

	/** 
	 * Method overrides the get magic method.
	 * @param $name The variable name to get.
	 */
	protected function _getData($name)
	{
		//Check to see if $name exists in model
		if(!array_key_exists($name, $this->_data))
		{
			$class = get_called_class();
			throw new \Framework\Exceptions\EDatabaseDriverException("Variable '$name' not in model '$class'.");
		}

		//Return _variables first
		if($this->_variables && array_key_exists($name, $this->_variables))
			return $this->_variables[$name];

		//Return _data by default
		return $this->_data[$name];
	}

	/**
	 * Method sets the variable array.
	 * @param $variables The variables array.
	 */
	protected function _setVariables($data)
	{
		//TODO: _setVariables special method that copies CStructures _variables to _data
		//TODO: If type is Structure, call _setVariable
		foreach($data as $key=>$val)
		{
			if($val instanceof \Framework\Database2\Model\DataType\CDataType)
			{
				echo 'DATATYPE ';
				die(__METHOD__);
			}
			else
				$this->$key = $val;
		}
	}
}
?>
