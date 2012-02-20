<?
define("MODEL_INSERT", 0x00);
define("MODEL_UPDATE", 0x01);

abstract class CModel implements IModel
{
	protected $_variables;
	protected $_originals;

	protected $_db;
	protected $_table;
	protected $_class;

	public function __construct($_db, $_table, $_class, $data, $pre='')
	{
		//Set db and table
		$this->_db    = $_db;
		$this->_table = $_table;
		$this->_class = $_class;

		$this->_variables = array();
		$this->_originals = array();

		//If pre is defined, then we will be doing updates and not saves...
		$vars = array();
		if(count($data))
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
				
				//Ignore submit button
				if($key != "submit")
					$vars[$key] = $v;
			}

			if($pre == '')
				$this->_originals = $vars;
			else
				$this->_variables = $vars;

			//Normalize any variables
			$this->normalize();
		}
	}

	static public function requireModel($model)
	{
		global $_conf;
		$file = $_conf['approotpath'] . "app/models/" . strtolower($model) . ".php";
		require_once($file);
	}

	static public function model($class)
	{
		return new $class(array());
	}

	abstract public function save();
	abstract public function update();
	abstract public function getID();

	//Bulk/Single operations
	abstract public function findAll($condition = array());
	abstract public function findOne($condition = array());
	abstract public function remove($condition = array());

	//Normalize data
	abstract protected function normalize();
	
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
