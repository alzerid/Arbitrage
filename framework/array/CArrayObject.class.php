<?
class CArrayObject
{
	protected $_data;

	public function __construct(&$arr=array())
	{
		$this->_data = &$arr;
	}

	public function toArray()
	{
		return $this->_data;
	}

	public function __get($name)
	{
		if(!array_key_exists($name, $this->_data) || $this->_data[$name] === NULL)
			return NULL;

		//If an array, return
		if(is_array($this->_data[$name]))
			return new CArrayObject($this->_data[$name]);

		return $this->_data[$name];
	}

	public function __set($name, $value)
	{
		$this->_data[$name] = $value;
	}

	static public function mergeArrayObject(CArrayObject $obj1, CArrayObject $obj2)
	{
		$ret = array_merge($obj1->toArray(), $obj2->toArray());
		return new CArrayObject($ret);
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
