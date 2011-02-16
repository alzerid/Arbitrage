<?
abstract class Model
{
	protected $_variables;

	public function __construct($data, $pre='')
	{
		foreach($data as $k=>$v)
		{
			if($pre != '')
			{
				$spos = strpos($k, $pre);
				if($spos === false)
					continue;

				$key = substr($k, strlen($pre));
			}
			else
				$key = $k;

			$this->_variables[$key] = $v;
		}
	}

	static public function requireModel($model)
	{
		global $_conf;
		$file = $_conf['approotpath'] . "models/" . strtolower($model) . ".php";
		require_once($file);
	}

	abstract public function update($model);
	abstract public function toForm();
	abstract public function populate();
	
	public function toArray()
	{
		return $this->_variables;
	}

	public function __get($name)
	{
		if(isset($this->_variables[$name]))
			return $this->_variables[$name];

		return NULL;
	}

	protected function _updateEntry($key, &$data)
	{
		if(array_key_exists($key, $this->_variables))
			$data[$k] = $this->$key;
	}
}
?>