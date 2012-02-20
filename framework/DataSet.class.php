<?
class DataSet
{
	protected $_variables;
	protected $_originals;
	protected $_id;

	public function __construct($id, $data)
	{
		$this->_variables = array();
		$this->_originals = array();
		$this->_id        = $id;

		$this->_originals = $data;
	}

	public function toArray()
	{
		$vars = array();

		//Go through the originals first
		foreach($this->_originals as $key=>$value)
			$vars[$key] = $this->_originals[$key];

		foreach($this->_variables as $key=>$value)
			$vars[$key] = $this->_variables[$key];

		return $vars;
	}

	public function getID()
	{
		return $this->_id;
	}

	public function __get($name)
	{
		if(isset($this->_variables[$name]))
			return $this->_variables[$name];

		if(isset($this->_originals[$name]))
			return $this->_originals[$name];

		return NULL;
	}

	public function __set($name, $value)
	{
		$this->_variables[$name] = $value;
	}

	public function __isset($name)
	{
		return ($this->$name !== NULL);
	}

	public function __unset($name)
	{
		unset($this->_originals[$name]);
		unset($this->_variables[$name]);
	}
}
?>
