<?
class CArrayObject
{
	private $_data;

	public function __construct(array &$arr)
	{
		$this->_data = &$arr;
	}

	public function toArray()
	{
		return $this->_data;
	}

	public function __get($name)
	{
		if($this->_data[$name] === NULL)
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
}
?>
