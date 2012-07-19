<?
namespace Arbitrage2\Base;

class CExtension
{
	protected $_name;
	protected $_path;
	protected $_config;

	public function __construct($path, $name)
	{
		$this->_path   = $path;
		$this->_name   = $name;
		$this->_config = NULL;
	}

	public function initialize()
	{
		//Load the config
		$config = new \CArbitrageExtensionConfig();
		$file   = glob($this->_path . "/config.*");
		if(count($file) == 0)
			throw new EArbitrageException("Unable to load config file for extension '{$this->_name}'.");

		//Load config for extension
		$config->initialize(dirname($file[0]));
		$config->load(basename($file[0]));
		$config = $config->toArray();

		//Get ext config
		$this->_config = new \CArbitrageConfigProperty($config['extension']);
		unset($config['extension']);

		//Merge this new config with the application config
		\CApplication::getConfig()->merge($config);
	}

	public function getConfig()
	{
		return $this->_config;
	}
}
?>
