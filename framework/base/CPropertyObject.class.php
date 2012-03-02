<?
class CPropertyObject implements Iterator
{
	protected $_variables;
	private $_position;
	private $_keys;
	private $_key;
	private $_cnt;

	public function __construct(&$variables = NULL)
	{
		if($variables !== NULL)
			$this->_variables = &$variables;
		else
			$this->_variables = array();

		//Iterator
		$keys = array_keys($this->_variables);
		$this->_position = 0;
		$this->_keys     = array_keys($this->_variables);
		$this->_cnt      = count($this->_keys);
		$this->_key      = ((isset($this->_keys[$this->_position]))? $this->_keys[$this->_position] : NULL);
	}

	public function __get($name)
	{
		if(!array_key_exists($name, $this->_variables))
			return NULL;

		if(is_array($this->_variables[$name]) && $this->_isAssoc($this->_variables[$name]))
			return new CArbitrageConfigProperty($this->_variables[$name]);

		return $this->_variables[$name];
	}

	public function __set($name, $val)
	{
		$this->_variables[$name] = $val;
	}

	public function toArray()
	{
		return $this->_variables;
	}

	/* Iterator Implementation */
	public function rewind()
	{
		$this->_position = 0;
		$this->_key      = ((isset($this->_keys[$this->_position]))? $this->_keys[$this->_position] : NULL);
	}

	public function current()
	{
		$variables = array_values($this->_variables);
		$sub    = $variables[$this->_position];

		if(is_array($sub))
			return new CArbitrageConfigProperty($sub);

		return $sub;
	}

	public function key()
	{
		return $this->_key;
	}
	
	public function next()
	{
		$this->_position++;
		$this->_key = ((isset($this->_keys[$this->_position]))? $this->_keys[$this->_position] : NULL);
	}

	public function valid()
	{
		return ($this->_position < $this->_cnt);
	}
	/* End Iterator Implementation */


	private function _isAssoc($arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

}
?>
