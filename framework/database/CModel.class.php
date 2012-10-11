<?
namespace Framework\Database;

//abstract class CModel extends \Framework\Model\CMomentoModel implements \Framework\Interfaces\IDatabaseModelStructure
class CModel extends \Framework\Database\Structures\CHashStructure
{
	static private $_TYPES = array();

	public function __construct($data=NULL)
	{
		\Framework\Model\CMomentoModel::__construct($data);
	}

	/**
	 * Skeleton method for default values of the model.
	 */
	static public function defaults()
	{
		throw new \Framework\Exceptions\EModelDataException("Model must implement defaults static method");
	}

	/**
	 * Method defines the types associated with the Model.
	 */
	static public function types($class=NULL)
	{
		$class = (($class===NULL)? get_called_class() : $class);
		if(!array_key_exists($class, self::$_TYPES))
		{
			$defaults = static::defaults();
			$types    = array();

			foreach($defaults as $key=>$val)
			{
				$type = gettype($val);
				switch($type)
				{
					case "object":
						if(!($val instanceof \Framework\Model\CModel) && !($val instanceof \Framework\Interfaces\IModelDataType))
							throw new \Framework\Exceptions\EModelDataException("Data point '$key' is not an object of type \\Framework\\Model\\CModel or \\Framework\\Interfaces\\IModelDataType but is of type '" . get_class($val) . "'.");

						$type = "object:" . "\\". get_class($val);
						break;

					case "NULL":
					case "unknown type":
					case "array":
					case "resource":
						throw new \Framework\Exceptions\EModelDataException("Unable to handle assigned type '$type' for variable '$key'.");
						break;
				}
				
				//Set type
				$types[$key] = $type;
			}

			//Set types
			self::$_TYPES[$class] = $types;
		}

		return self::$_TYPES[$class];
	}

	/**
	 * Method converts the model to an array.
	 * @return array The array representing this model.
	 */
	public function toArray()
	{
		$ret = array();
		foreach($this->_data as $key=>$value)
		{
			if($value instanceof \Framework\Database\CModel)
				$value = $value->toArray();
			elseif($value instanceof \Framework\Interfaces\IDatabaseModelStructure)
			{
				//Convert the struct to the native driver type
				$struct = $this->_driver->convertModelStructureToNativeStructure($this->_data[$key]);
				$value  = $struct->toArray();
			}
			elseif($value instanceof \Framework\Interfaces\IModelDataType)
				$value = (string) $value;

			//Set key
			$ret[$key] = $value;
		}

		return $ret;
	}

	/**
	 * Method returns the query.
	 */
	public function getQuery()
	{
		$ret = array();
		foreach($this->_data as $key=>$value)
		{
			$value = $this->$key;
			if($value instanceof \Framework\Database\CModel)
				$value = $value->getQuery();
			elseif($value instanceof \Framework\Interfaces\IDatabaseModelStructure)
			{
				//Convert Native structure to Model
				$struct = $this->_driver->convertModelStructureToNativeStructure($value);
				$value  = $struct->getQuery();
			}

			//Assign values
			$ret[$key] = $value;
		}

		return ((count($ret)===0)? NULL : $ret);
	}

	/**
	 * Method returns the updated query.
	 * @param $prepend The key string to prepend.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery($prepend=NULL)
	{
		$ret = array();
		foreach($this->_data as $key=>$val)
		{
			//Item in variables array
			$value = NULL;
			$pkey  = (($prepend!==NULL)? "$prepend.$key" : $key);
			if(array_key_exists($key, $this->_variables))
				$value = $this->_variables[$key];
			elseif($val instanceof \Framework\Database\CModel)
				$value = $val->getUpdateQuery($pkey);
			elseif($val instanceof \Framework\Interfaces\IDatabaseModelStructure)
			{
				//Convert the struct to the native driver type
				$struct = $this->_driver->convertModelStructureToNativeStructure($this->_data[$key]);
				$value  = $struct->getUpdateQuery($pkey);
			}

			//Set data point to ret
			if($value !== NULL)
			{
				if(is_array($value))
					$ret = array_merge($ret, $value);
				else
					$ret[$pkey] = $value;
			}
		}

		return ((count($ret)===0)? NULL : $ret);
	}

	/**
	 * Method merges _variables into _data.
	 */
	public function merge()
	{
		//Iterate through and merge
		foreach($this->_data as $key=>$val)
		{
			if($val instanceof \Framework\Interfaces\IDatabaseModelStructure)
				$val->merge();
			elseif(array_key_exists($key, $this->_variables))
				$this->_data[$key] = $this->_variables[$key];
		}

		//Clear variables
		$this->clear();
	}

	/**
	 * Method converts the database model to a simple \Framework\Model\CModel
	 * @return Returns the \Framework\Model\CModel representation of this DatabaseModel.
	 */
	public function convertToBaseModel()
	{
		//TODO: Recursive convert any models
		$model = new \Framework\Model\CModel;

		//Set from data
		foreach($this->_data as $key=>$val)
			$model->$key = $this->$key;

		return $model;
	}

	/**
	 * Method returns the driver.
	 * @return Returns the driver.
	 */
	public function getDriver()
	{
		return $this->_driver;
	}

	/**
	 * Method overloaded when setting items within the model.
	 * @param $name The attribute name in the model.
	 * @param $data The data to set.
	 */
	protected function _setData($name, $data)
	{
		//Ensure originals is set
		if(!array_key_exists($name, $this->_data))
			throw new \Framework\Exceptions\EModelDataException("Attribute '$key' is not defined in this model '\\" . get_class($this) . "'");

		//Check if data type and structure
		if($this->_data[$name] instanceof \Framework\Interfaces\IDatabaseModelStructure)
		{
			var_dump($name, $data);
			throw new \Exception("Not yet implemented");
		}
		elseif($this->_data[$name] instanceof \Framework\Interfaces\IModelDataType)
		{
			$class = get_class($this->_data[$name]);
			$this->_variables[$name] = new $class($data);
		}
		elseif(!is_object($this->_data[$name]))
			$this->_variables[$name] = $data;
		else
			throw new \Framework\Exceptions\EModelDataException("Unable to handle data!!");
	}

	/**
	 * Method sets originals only if key exists in default.
	 * @param $data The data to set.
	 */
	protected function _setModelData($data)
	{
		//TODO: Types cast everything

		//Set data to defaults
		$this->_data = static::defaults();
		$types       = static::types();
		$data        = (($data===NULL)? array() : $data);
		foreach($this->_data as $key=>$val)
		{
			if(!array_key_exists($key, $data) || $data[$key] === NULL)
				continue;

			//Check to see what type we are
			if($this->_data[$key] instanceof \Framework\Database\CModel)
			{
				$model = $this->_data[$key];
				$model->setDriver($this->_driver);
				$model->_setModelData($data[$key]);
			}
			elseif($this->_data[$key] instanceof \Framework\Interfaces\IDatabaseModelStructure)
			{
				//Get struct
				$struct = $this->_data[$key];
				$struct->setDriver($this->_driver);
				$struct->_setModelData($data[$key]);

				//Convert to model structure
				if($this->_driver)
					$this->_data[$key] = $this->_driver->convertNativeStructureToModelStructure($struct);
			}
			elseif($this->_data[$key] instanceof \Framework\Interfaces\IModelDataType)
			{
				if($this->_driver)
					$this->_variables[$key] = $this->_driver->convertNativeDataTypeToModelDataType($data[$key]);
				else
				{
					$this->_variables[$key] = clone $this->_data[$key];
					$this->_variables[$key]->setValue($data[$key]);
				}
			}
			else
				$this->_variables[$key] = $data[$key];
		}
	}
}
?>
