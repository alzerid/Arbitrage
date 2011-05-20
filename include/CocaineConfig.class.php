<?
class CocaineConfig
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
			self::$_instance = new CocaineConfig;

		return self::$_instance;
	}

	public function initialize($root, $env)
	{
		$this->_root = $root . "/$env";
		$this->_env  = $env;

		//Setup paths
		$this->_variables['fwrootpath']  = COCAINE_FW_PATH;
		$this->_variables['approotpath'] = realpath("$root/../") . "/";
	}

	public function getVariables()
	{
		return $this->_variables;
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
		return ((isset($this->_variables[$name]))? $this->_variables[$name] : NULL);
	}

	private function _loadYAML($file)
	{
		$conf = yaml_parse_file("{$this->_root}/$file");
		$this->_variables = array_merge($this->_variables, $conf);
	}
}
?>
