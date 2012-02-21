<?
class CArbitrageConfig implements ISingleton
{
	static private $_instance = NULL;
	protected $_variables;
	protected $_env;
	protected $_root;

	public function __construct()
	{
		$this->_variables = array();
	}

	static public function getInstance()
	{
		if(self::$_instance == NULL)
			self::$_instance = new CArbitrageConfig;

		return self::$_instance;
	}

	public function initialize($root, $env)
	{
		$this->_root = $root . "/$env";
		$this->_env  = $env;

		//Setup _internals
		$this->_variables['_internals']                = array();
		$this->_variables['_internals']['fwrootpath']  = ARBITRAGE_FW_PATH;
		$this->_variables['_internals']['approotpath'] = realpath("$root/../") . "/";

		//Setup view and layout path
		$this->_variables['_internals']['viewpath']   = realpath($this->_variables['_internals']['approotpath'] . "app/views/");
		$this->_variables['_internals']['layoutpath'] = realpath($this->_variables['_internals']['approotpath'] . "app/views/layout/");
	}

	public function getEnvironment()
	{
		return $this->_env;
	}

	public function toArray()
	{
		return $this->_variables;
	}

	public function getVariables()
	{
		return $this->toArray();
	}

	public function load($filename)
	{
		//Determine if it is a YAML file or PHP file
		$file = basename($filename);
		$file = explode('.', $file);
		$ext  = strtolower($file[count($file)-1]);

		switch($ext)
		{
			case "yaml":
			case "yml":
				$this->_loadYAML($filename);
				break;
		}
	}

	public function __get($name)
	{
		$prop = new CArbitrageConfigProperty($this->_variables);
		return $prop->$name;
	}

	public function __set($name, $val)
	{
		$this->_variables[$name] = $val;
	}

	private function _loadYAML($file)
	{
		$conf = yaml_parse_file("{$this->_root}/$file");
		$this->_variables = array_merge($this->_variables, $conf);
	}
}

class CArbitrageConfigProperty implements Iterator
{
	private $_config;
	private $_position;
	private $_keys;
	private $_key;
	private $_cnt;

	public function __construct(&$config)
	{
		$this->_config   = &$config;

		//Iterator
		$keys = array_keys($this->_config);
		$this->_position = 0;
		$this->_keys     = array_keys($this->_config);
		$this->_cnt      = count($this->_keys);
		$this->_key      = ((isset($this->_keys[$this->_position]))? $this->_keys[$this->_position] : NULL);
	}

	public function __get($name)
	{
		if(!array_key_exists($name, $this->_config))
			return NULL;

		if(is_array($this->_config[$name]) && $this->_isAssoc($this->_config[$name]))
			return new CArbitrageConfigProperty($this->_config[$name]);

		return $this->_config[$name];
	}

	public function toArray()
	{
		return $this->_config;
	}

	public function __set($name, $val)
	{
		$this->_config[$name] = $val;
	}

	/* Iterator Implementation */
	public function rewind()
	{
		$this->_position = 0;
		$this->_key      = ((isset($this->_keys[$this->_position]))? $this->_keys[$this->_position] : NULL);
	}

	public function current()
	{
		$config = array_values($this->_config);
		$sub    = $config[$this->_position];

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
