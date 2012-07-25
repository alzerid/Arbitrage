<?
namespace Arbitrage2\Config;
use \Arbitrage2\Utils\CArrayObject;

class CArbitrageConfig extends CArbitrageConfigProperty
{
	protected $_env;    //The current configuration environment
	protected $_root;   //Root path for configuration

	public function __construct($root, $env, &$config=array())
	{
		$this->_root = $root . "/$env";
		$this->_env  = $env;

		parent::__construct($config);
	}

	/*public function initialize(array &$config=array())
	{
		//Create config
		$config = array();

		//Setup _internals
		$this->_variables['_internals']                  = array();
		$this->_variables['_internals']['fwrootpath']    = ARBITRAGE2_FW_PATH;
		$this->_variables['_internals']['approotpath']   = realpath("$root/../") . "/";
		$this->_variables['_internals']['appconfigpath'] = realpath("$root/../") . "/";


		//Setup view and layout path
		$this->_variables['_internals']['viewpath']   = realpath($this->_variables['_internals']['approotpath'] . "app/views/") . "/";
		$this->_variables['_internals']['layoutpath'] = realpath($this->_variables['_internals']['approotpath'] . "app/views/layout/") . "/";

		//Setup arbitrage
		$config['server']              = array();
		$config['server']['debugMode'] = false;            //Default debug mode is off

		//Initialize
		parent::initialize($config);
	}*/

	public function getEnvironment()
	{
		return $this->_env;
	}

	public function getPath()
	{
		return $this->_root;
	}

	public function load($filename)
	{
		//Ensure full path
		$path = $this->_root . "/" . basename($filename);

		//Get loader
		$loader = CArbitrageConfigLoader::getLoader($path);
		$loader->load($this->_data);
	}
}

class CArbitrageConfigProperty extends CArrayObject
{
	/* Magic Methods */
	protected function _get($name)
	{
		if(!array_key_exists($name, $this->_data))
			return NULL;

		if(is_array($this->_data[$name]) && $this->_isAssoc($this->_data[$name]))
			return new CArbitrageConfigProperty($this->_data[$name]);

		return $this->_data[$name];
	}
	/* End Magic Methods */

	public function merge(array $config)
	{
		foreach($config as $key=>$val)
		{
			if(isset($this->$key))
			{
				if($this->$key instanceof CArbitrageConfigProperty)
					$this->$key->merge($val);
				else
					$this->$key = $val;
			}
			else
				$this->$key = $val;
		}
	}
}
?>
