<?
namespace Arbitrage2\Model2;

class CModelData
{
	static private $_TYPES = array();  //Typecasts

	private $_originals;
	private $_variables;
	protected $_path;

	public function __construct(array &$originals=array(), array &$variables=array())
	{
		//Get defaults
		if(count($originals) === 0)
			$originals = static::defaults();

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

		//Typecast
		foreach($types as $key=>$type)
		{
			//check type
			if(!array_key_exists($key, $data))
				$data[$key] = $defaults[$key];
			elseif(strpos($type, 'object:') === 0)
			{
				$obj = substr($type, 7);
				$cls = "";
				if(($idx = strpos($obj, ":")) !== false)
				{
					$cls = substr($obj, $idx+1);
					$obj = substr($obj, 0, $idx);
				}

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
						throw new EModelData('Object not correct type!');
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
					else
						throw new EModelData("Unable to handle object type '$obj'");
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
				die("ASSOC");
			}
			else
			{
				die("NOT ASSOCIATIVE");
			}
		}

		//Return value not reference
		$ret = $val;

		return $ret;
	}

	static public function isAssoc(array $arr)
	{
		return (is_array($arr) &&  array_keys($arr) !== range(0, count($arr) - 1));
	}

	static public function defaults()
	{
		return static::defaults();
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

					if($cls == 'Arbitrage2\Model2\CModelArrayData')
						$type .= ":{$val->getClass()}";
				}

				$types[$key] = $type;
			}

			self::$_TYPES[$class] = $types;
		}

		return self::$_TYPES[$class];
	}
}
?>
