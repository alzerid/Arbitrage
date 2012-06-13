<?
namespace Arbitrage2\Model2;

class CModelData implements \ArrayAccess
{
	static private $_TYPES    = array();  //Typecasts
	static private $_DEFAULTS = array();  //Cached defaults

	protected $_originals;    //The original values provided to the CModelData
	protected $_variables;    //The updated variables that were set programatically
	protected $_path;         //The array path from the root CModel class

	public function __construct()
	{
		//Set originals 
		$this->_originals = self::defaults();  //set defaults to originals
		$this->_variables = array();           //empty variable set
		$this->_path      = array();

		if($this->_originals === NULL)
			throw new EModelDataException("Unable to get default values for '" . get_called_class() . "'.");
	}

	/** Object Access Pattern **/

	public function __get($name)
	{
		return $this->_getData($name);
	}

	public function __set($name, $val)
	{
		$this->_setData($name, $val);
	}

	public function __isset($name)
	{
		return $this->_issetData($name);
	}

	public function __unset($name)
	{
		$this->_unsetData($name);
	}
	/** End Object Acecss Pattern **/

	/* ArrayAccess methods */
	public function offsetExists($offset)
	{
		return $this->{$offset};
	}

	public function offsetGet($offset)
	{
		return $this->{$offset};
	}

	public function offsetSet($offset, $val)
	{
		$this->{$offset} = $val;
	}

	public function offsetUnset($offset)
	{
		unset($this->{$offset});
	}

	/* End ArrayAccess methods */

	public function reset()
	{
		$this->_variables = array();
	}

	//Grab only updated entries
	public function toArrayUpdated()
	{
		$ret = array();
		foreach($this->_originals as $key => $val)
		{
			if($val instanceof CModelData)
			{
				$data = $val->toArrayUpdated();
				if(count($data) > 0)
					$ret[$key] = $data;
			}
			elseif(array_key_exists($key, $this->_variables))
				$ret[$key] = $this->_variables[$key];
		}

		return $ret;
	}

	public function toArray()
	{
		//Convert Data points to an array
		$ret = array();

		foreach($this->_originals as $key => $val)
		{
			if($val instanceof CModelData)
				$ret[$key] = $val->toArray();
			else
				$ret[$key] = ((array_key_exists($key, $this->_variables))? $this->_variables[$key] : $val);
		}

		return $ret;
	}

	protected function _setModelData(array &$originals=array())
	{
		$class = get_called_class();
		$types = self::$_TYPES[$class];
		foreach($originals as $key=>$val)
		{
			if(!array_key_exists($key, $this->_originals))
				continue;

			$type = self::$_TYPES[$class][$key];
			if(strpos($type, "object") === 0)   //Object handle
			{
				$cls = substr($type, 7);

				//HACK: Ignore mongo objects 
				if(strpos(strtolower($cls), 'mongo') !== false)
					$this->_originals[$key] = $val;
				elseif($this->_originals[$key] instanceof CModelData && $val !== NULL) //Model data, recurse
					$this->_originals[$key]->_setModelData($val);
			}
			else  //Primitive type
			{
				settype($val, $type);
				$this->_originals[$key] = $val;
			}
		}
	}

	protected function _merge()
	{
		//Merge the variables to the originals
		foreach($this->_originals as $key=>$val)
		{
			if($val instanceof CModelData)
				$val->_merge();
			elseif(array_key_exists($key, $this->_variables))
				$this->_originals[$key] = $this->_variables[$key];
		}

		//Reset _variables
		$this->reset();
	}

	static public function defaults()
	{
		//TODO: Only accept known objects --EMJ

		//Get defaults from CModelData
		$defaults = static::defaults();
		$class    = get_called_class();

		//Get types if not set already
		if(!isset(self::$_TYPES[$class]))
		{
			self::$_TYPES[$class] = array();
			foreach($defaults as $key=>$val)
			{
				$type = gettype($val);
				if(is_object($val))
					$type .= ":" . get_class($val);
					
				self::$_TYPES[$class][$key] = $type;
			}
		}

		return $defaults;
	}

	static protected function _types()
	{
		$class = get_called_class();
		return self::$_TYPES[$class];
	}

	protected function _getData($name)
	{
		//First check _varaibles
		if(array_key_exists($name, $this->_variables) && !($this->_originals[$name] instanceof CModelData))
			return $this->_variables[$name];
		elseif(array_key_exists($name, $this->_originals))
			return $this->_originals[$name];

		throw new EModelException("Data point '$name' not in definitions.");
	}

	protected function _setData($name, $val)
	{
		if(!array_key_exists($name, $this->_originals))
			throw new EModelDataException("Data point '$name' not in definitions.");

		if($this->_originals[$name] instanceof CModelData)
			throw new EModelDataException("Unable to set '$name' because it is of type CModelData!");

		//type cast
		$types = static::_types();
		if(!preg_match('/^object:/', $types[$name]))
			settype($val, $types[$name]);

		$this->_variables[$name] = $val;
	}

	protected function _issetData($name)
	{
		return array_key_exists($name, $this->_originals) || array_key_exists($name, $this->_variables);
	}

	protected function __unsetData($name)
	{
		if(array_key_exists($name, $this->_variables))
			unset($this->_variables[$name]);
	}
}
?>
