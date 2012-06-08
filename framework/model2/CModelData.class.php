<?
namespace Arbitrage2\Model2;

class CModelData implements \ArrayAccess
{
	static private $_TYPES = array();  //Typecasts

	private $_originals;      //The original values provided to the CModelData
	private $_variables;      //The updated variables that were set programatically
	protected $_path;         //The array path from the root CModel class

	public function __construct(array &$originals=array(), array &$variables=array())
	{
		//If originals not provided, we are probably programatically instantiated
		if(count($originals) === 0)
			$originals = self::defaults();

		if($originals === NULL)
			throw new EModelDataException("Unable to get default values for '" . get_called_class() . "'.");

		$this->_setData($originals, $variables);
		$this->_path = array();
	}

	public function __get($name)
	{
		//Check if variables is set
		$ret = NULL;
		if(array_key_exists($name, $this->_variables))
			$ret = $this->_getData($this->_variables[$name]);
		else if(array_key_exists($name, $this->_originals))
			$ret = $this->_getData($this->_originals[$name]);
		else
			throw new EModelException("Data point '$name' not in definitions.");

		return $ret;
	}

	public function __set($name, $val)
	{
		if(!array_key_exists($name, $this->_originals))
			throw new EModelException("Data point '$name' not in definitions.");

		//type cast
		$types = static::_types();
		if(!preg_match('/^object:/', $types[$name]))
			settype($val, $types[$name]);

		$this->_variables[$name] = $val;
	}

	public function __isset($name)
	{
		$ret = array_key_exists($name, $this->_originals);
		if(!$ret)
			$ret = array_key_exists($name, $this->_variables);

		return $ret;
	}

	/* ArrayAccess methods */
	public function offsetExists($offset)
	{
		//Check if exists in _variables
		if(array_key_exists($offset, $this->_variables))
			return true;

		if(array_key_exists($offset, $this->_originals))
			return true;

		return false;
	}

	public function offsetGet($offset)
	{
		if(array_key_exists($offset, $this->_variables))
			return $this->_variables[$offset];

		if(array_key_exists($offset, $this->_originals))
			return $this->_originals[$offset];

		return NULL;
	}

	public function offsetSet($offset, $val)
	{
		throw new EMOdelData('offsetSet not coded');
	}

	public function offsetUnset($offset)
	{
		throw new EMOdelData('offsetUnset not coded');
	}

	/* End ArrayAccess methods */

	public function getOriginalData()
	{
		return $this->_originals;
	}

	public function getUpdatedData()
	{
		$vars  = $this->_variables;
		$types = self::_types();

		//Grab object var
		foreach($types as $key=>$val)
		{
			if(strpos($val, 'object:') === 0)
			{
				$obj = substr($val, 7);
				if(get_parent_class($obj) == 'Arbitrage2\Model2\CModelData')
					$vars[$key] = $this->_originals[$key]->getUpdatedData();
				elseif($obj == 'Arbitrage2\Model2\CModelArrayData')
				{
					die("ARRAY DATA");
				}
			}
		}

		return $vars;
	}

	public function reset()
	{
		$this->_variables = array();
	}

	protected function _setData(array &$originals=array(), array &$variables=array())
	{
		//foreach($originals as $
		$this->_originals = $originals;
		$this->_variables = $variables;
	}

	protected function _merge()
	{
		//Go through each originals and set if variables have a value
		foreach($this->_originals as $key=>$val)
		{
			if(array_key_exists($key, $this->_variables))
				$this->_originals[$key] = $this->_variables[$key];
			elseif($val instanceof CModelData)
				$val->_merge();
		}

		//Reset _variables
		$this->reset();
	}

	protected function _normalizeData()
	{
		//TODO: Normalize Variables

		//Get types
		$defaults = self::defaults();
		$types    = self::_types();
		$data     = &$this->_originals;

		//Go through types and do special operations on internal classes
		foreach($types as $key=>$type)
		{
			//check type
			if(!array_key_exists($key, $data))
				$data[$key] = $defaults[$key];
			elseif(strpos($type, 'object:') === 0)  //Property value is an object
			{
				$obj = substr($type, 7);
				$cls = "";
				if(($idx = strpos($obj, ":")) !== false)
				{
					$cls = substr($obj, $idx+1);
					$obj = substr($obj, 0, $idx);
				}

				//If it is a CModelArrayData, do some special operations
				if($obj === 'Arbitrage2\Model2\CModelArrayData')
				{
					$list = new CModelArrayData($cls);
					foreach($data[$key] as $k=>$v)
					{
						$val = new $cls($v);
						$val->_normalizeData();
						$list[$k] = $val;
					}

					//Set data
					$data[$key] = $list;
				}
				elseif(is_object($data[$key]))
				{
					if(get_class($data[$key]) !== $obj)
						throw new EModelDataException('Object not correct type!');
				}
				else
				{
					$parent = get_parent_class($obj);
					if($parent === 'Arbitrage2\Model2\CModelData')
					{
						//Create new object
						$obj = new $obj;

						//Setup path
						$obj->_path = array_merge($this->_path, array($key));

						//Set data and normalize
						$obj->_setData($data[$key]);
						$obj->_normalizeData();
						$data[$key] = $obj;
					}
					elseif($data[$key] instanceof Arbitrage2\Model2\CModelHashData)
					{
						die('key');
					}
					else
					{
						var_dump($data[$key]);
						throw new EModelDataException("Unable to handle object type '$obj'");
					}
				}
			}
			elseif($type != gettype($data[$key]))
				settype($data[$key], $type);
		}
	}

	private function &_getData(&$val)
	{
		//Check type
		if(is_array($val))
		{
			if(self::isAssoc($val))
			{
				var_dump($val, $this);
				die("CModelData: ASSOC");
			}
			/*else
			{
				die("NOT ASSOCIATIVE");
			}*/
		}

		//Return value not reference
		$ret = $val;

		return $ret;
	}

	static public function isAssoc(array $arr)
	{
		return (count($arr) > 0 && is_array($arr) &&  array_keys($arr) !== range(0, count($arr) - 1));
	}

	static public function defaults()
	{
		//Get defaults from CModelData
		$defaults = static::defaults();
		$class    = get_called_class();
		var_dump($defaults);

		//Get types if not set already
		if(!isset(self::$_TYPES[$class]))
		{
			self::$_TYPES[$class] = array();
			foreach($defaults as $key=>$val)
				self::$_TYPES[$class][$key] = gettype($val);
		}

		return $defaults;
	}

	static protected function _types()
	{
		$class = get_called_class();

		//Ensure $_TYPES existance
		if(empty(self::$_TYPES[$class]))
		{
			$defaults = static::defaults();
			$types    = array();
			foreach($defaults as $key=>$val)
			{
				$type = gettype($val);
				if($type == "object")
				{
					$cls  = get_class($val);
					$type = "object:" . $cls;

					if($val instanceof Arbitrage2\Model2\CModelArrayData)  //CModelHashData is also included here
						$type .= ":{$val->getClass()}";

					var_dump($type, $val);
				}

				$types[$key] = $type;
			}

			self::$_TYPES[$class] = $types;
		}

		return self::$_TYPES[$class];
	}
}
?>
