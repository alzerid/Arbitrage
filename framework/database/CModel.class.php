<?
namespace Framework\Database;

abstract class CModel extends \Framework\Model\CMomentoModel implements \Framework\Interfaces\IDatabaseModelStructure
{
	static private $_TYPES = array();
	private $_driver;

	public function __construct()
	{
		$this->_driver = NULL;
		parent::__construct();
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
	 * Method instantiates a Defined Model.
	 * 
	 */
	static public function instantiate($data=NULL, \Framework\Database\CDatabaseDriver $driver=NULL)
	{
		//Instantiate new model object
		$class = get_called_class();
		$obj   = new $class;

		//Set driver and data
		$obj->_driver = $driver;
		$obj->_setModelData($data);

		return $obj;
	}

	/**
	 * Method converts the model to an array.
	 * @return array The array representing this model.
	 */
	public function toArray()
	{
		die(__METHOD__);
	}

	/**
	 * Method returns the updated query.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery()
	{
		//TODO: Handle ModelStructures

		$ret = array();
		foreach($this->_data as $key=>$val)
		{
			if(array_key_exists($key, $this->_variables))
			{
				//Figure out how to handle this data
				if($val instanceof \Framework\Interfaces\IModelDataType)
					$ret[$key] = $this->_driver->convertModelDataTypeToNativeDataType($this->_variables[$key]);
				elseif(!is_object($val))
				{
					//TODO: set_type ?? Or set_type when we actually set the data in _setData ???
					$ret[$key] = $val;
				}
				else
				{
					var_dump($key, $val);
					throw new \Framework\Exceptions\EModelDataException("Unable to handle data type.");
				}
			}
			elseif($val instanceof \Framework\Database\CModel)
				$ret[$key] = $val->getUpdateQuery();
			elseif($val instanceof \Framework\Interfaces\IDatabaseModelStructure)
			{
				//Convert the struct to the native driver type
				$struct = $this->_driver->convertModelStructureToNativeStructure($this->_data[$key]);
				$query  = $struct->getUpdateQuery();

				//If query is NULL, there are no updates
				if($query !== NULL)
					$ret[$key] = $struct->getUpdateQuery();
			}
		}

		return $ret;
	}

	/**
	 * Method resets to original.
	 */
	public function clear()
	{
		$this->_variables = array();
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
		if($this->_data[$name] instanceof \Framework\Interfaces\IModelDataType)
		{
			$class = get_class($this->_data[$name]);
			$this->_variables[$name] = $class::instantiate($data);
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
			//TODO: Handle CModel instances of $val
			if(!array_key_exists($key, $data) || $data[$key] === NULL)
				continue;

			if($this->_data[$key] instanceof \Framework\Model\CModel)
				$this->_data[$key]->_setModelData($data[$key]);
			elseif($this->_data[$key] instanceof \Framework\Interfaces\IModelDataType)
				$this->_data[$key]->setValue($this->_driver->convertNativeDataTypeToModelDataType($data[$key]));
			else
				$this->_data[$key] = $data[$key];
		}
	}
}
?>
