<?
namespace Arbitrage2\Utils;

class CArrayObject implements \ArrayAccess, \Iterator
{
	protected $_data;
	private $_position;
	private $_keys;

	public function __construct(&$arr=array())
	{
		$this->_data     = &$arr;
		$this->_position = -1;
		$this->_keys     = array();
	}

	/* Start Property Access Methods */
	public function __get($name)
	{
		return $this->_get($name);
	}

	public function __set($name, $value)
	{
		$this->_set($name, $value);
	}

	public function __isset($name)
	{
		return $this->_isset($name);
	}

	public function __unset($name)
	{
		$this->_unset($name);
	}

	protected function _get($name)
	{
		if(!array_key_exists($name, $this->_data) || $this->_data[$name] === NULL)
			return NULL;

		//If an array, return
		if(is_array($this->_data[$name]))
			return new CArrayObject($this->_data[$name]);

		return $this->_data[$name];
	}

	protected function _set($name, $value)
	{
		$this->_data[$name] = $value;
	}

	protected function _isset($name)
	{
		return array_key_exists($name, $this->_data);
	}

	protected function _unset($name)
	{
		if(isset($this->_data[$name]))
			unset($this->_data[$name]);
	}
	/* End Property Access Methods */

	/* Array Access Methods */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->_data);
	}

	public function offsetGet($offset)
	{
		$data = $this->_data[$offset];
		if(is_array($data))
			$data = new CArrayObject($data);

		return $data;
	}

	public function offsetSet($offset, $val)
	{
		$this->_data[$offset] = $val;
	}

	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}
	/* End Array Access Methods */

	/* Iterator Implementation */
	public function rewind()
	{
		$this->_position = -1;
	}

	public function current()
	{
		return $this->_data[$this->_keys[$this->_position]];
	}

	public function key()
	{
		return $this->_keys[$this->_position];
	}

	public function next()
	{
		++$this->_position;
	}

	public function valid()
	{
		if($this->_position == -1)
			$this->_setupIterator();

		return isset($this->_keys[$this->_position]);
	}
	/* End Iterator Implementation */

	public function flatten($depth=-1)
	{
		$ret = self::flattenArray($this->_data, $depth);
		return new CArrayObject($ret);
	}

	public function toArray()
	{
		return $this->_data;
	}

	public function push($new)
	{
		$this->_data[] = $new;
	}

	public function pop()
	{
		return array_pop($this->_data);
	}

	public function xpath($path)
	{
		static $exceptions = NULL;

		if($exceptions == NULL)
			$exceptions = array('//', '.', '..', '@');

		$xpath = $this->_xpathParse($path);
		$data  = $this->_data;
		$ret   = NULL;
		foreach($xpath as $x)
		{
			if(in_array($x, $exceptions))
				throw new EArrayObjectException("XPath parsing for '$x' not implemented.");

			if($x === "/")
				continue;

			if(isset($data[$x]))
			{
				$ret  = $data[$x];
				$data = $data[$x];
			}
			else
				return NULL;
		}

		return $ret;
	}

	protected function _isAssoc($arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	private function _setupIterator()
	{
		$this->_keys     = array_keys($this->_data);
		$this->_position = 0;
	}

	static public function flattenArray(array &$arr, $depth=-1, $pre="")
	{
		$ret = array();
		foreach($arr as $key=>$val)
		{
			$key = (($pre != "")? "$pre{$key}" : $key);
			if(is_array($val) && ($depth>0 || $depth==-1))
				$ret = array_merge($ret, self::flattenArray($val, (($depth>0)? $depth-1 : -1), "$key."));
			else
				$ret[$key] = $val;
		}

		return $ret;
	}

	static public function mergeArrayObject(CArrayObject $obj1, CArrayObject $obj2)
	{
		$ret = array_merge($obj1->toArray(), $obj2->toArray());
		return new CArrayObject($ret);
	}

	static public function mergeRecursiveUnique(CArrayObject $obj1, CArrayObject $obj2)
	{
		$merged = $obj1->_toArrayReference();
		$arr2   = $obj2->_toArrayReference();

		if(is_array($arr2))
		{
			foreach($arr2 as $key => $val)
			{
				if(is_array($arr2[$key]))
				{
					if(is_array($merged[$key]))
					{
						$ret = self::mergeRecursiveUnique(new CArrayObject($merged[$key]));
					}
					else
						$ret = $arr2[$key];

					$merged[$key] = $ret->_toArrayReference();
				}
				else
					$merged[$key] = $val;
			}
		}

		return new CArrayObject($merged);
	}

	protected function &_toArrayReference()
	{
		return $this->_data;
	}

	private function _xpathParse($path)
	{
		$special = array('/', '//', '.', '..', '@');
		$xpath   = array();
		$prev    = '';
		$len     = strlen($path);
		$idx     = 0;
		while($idx < $len)
		{
			if($path[$idx] === "/" && $prev === "/")
				$xpath[count($xpath)-1] .= $path[$idx];
			elseif($path[$idx] === "/")
				$xpath[] = $path[$idx];
			elseif($path[$idx] === ".")
				die("Implement . parsing");
			elseif($path[$idx] === "..")
				die("Implement .. parsing");
			elseif($path[$idx] === "@")
				die("Implement attributes");
			else
			{
				if(count($xpath)-1  == 0 || in_array($prev, $special))
					$xpath[] = "";

				$xpath[count($xpath)-1] .= $path[$idx];
			}

			$prev = $xpath[count($xpath)-1];
			$idx++;
		}

		return $xpath;
	}
}
?>
