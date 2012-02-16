<?
class ArbitrageConfig
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
			self::$_instance = new ArbitrageConfig;

		return self::$_instance;
	}

	public function initialize($root, $env)
	{
		$this->_root = $root . "/$env";
		$this->_env  = $env;

		//Setup paths
		$this->_variables['fwrootpath']  = ARBITRAGE_FW_PATH;
		$this->_variables['approotpath'] = realpath("$root/../") . "/";
	}

	public function getEnvironment()
	{
		return $this->_env;
	}

	public function getVariables()
	{
		return $this->_variables;
	}

	/*public function setVariable($key, $value)
	{
		$this->_variables[$key] = $value;
	}*/

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
		$prop = new ArbitrageConfigProperty($this->_variables);
		return $prop->$name;
	}

	public function __set($name, $val)
	{
		$this->_config[$name] = $val;
	}

	private function _loadYAML($file)
	{
		$conf = yaml_parse_file("{$this->_root}/$file");
		$this->_variables = array_merge($this->_variables, $conf);
	}
}

class ArbitrageConfigProperty
{
	private $_config;

	public function __construct(&$config)
	{
		$this->_config = &$config;
	}

	public function __get($name)
	{
		if(!array_key_exists($name, $this->_config))
			return NULL;

		if(is_array($this->_config[$name]) && $this->_isAssoc($this->_config[$name]))
			return new ArbitrageConfigProperty($this->_config[$name]);

		return $this->_config[$name];
	}

	public function __set($name, $val)
	{
		$this->_config[$name] = $val;
	}

	private function _isAssoc($arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}
?>
