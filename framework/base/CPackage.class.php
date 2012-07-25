<?
namespace Arbitrage2\Base;
use \Arbitrage2\Config\CArbitrageConfig;

class CPackage
{
	private $_path;       //The absolute filesystem path the package resides
	private $_namespace;  //Root namespace describing this package
	private $_config;     //The configuration object
	private $_parent;     //The parent package that loaded this package

	public function __construct($parent=NULL)
	{
		$this->_path      = '';
		$this->_namespace = '';
		$this->_config    = NULL;
		$this->_parent    = $parent;
	}

	/**
	 * Initializes the package.
	 * @param string $path The path where the package resides in.
	 * @param string $namespace The namespace associated with the object.
	 */
	public function initialize($path, $namespace)
	{
		$this->_path      = $path;
		$this->_namespace = $namespace;

		//Load the config
		$this->_loadConfiguration();
	}

	/**
	 * Returns the config object associated with the package.
	 * @return \Arbitrage2\Base\CArbitrageConfig Returns the arbitrage config object.
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * Method gets the parent package.
	 * @return \CArbitrage\Base\CPackage Returns the parent package.
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Method returns the file system path of where the package is located.
	 * @return string Returns the file system path.
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Loads the configuration file.
	 */
	private function _loadConfiguration()
	{
		//Setup paths
		$env    = ((isset($_SERVER['ARBITRAGE2_ENVIRONMENT']))? $_SERVER['ARBITRAGE2_ENVIRONMENT'] : 'development');
		$cpath  = $this->_path . "/" . CKernel::getInstance()->convertArbitrageNamespaceToPath($this->_namespace . ".config");
		$file   = $cpath . "/config"  . '.php';

		//Create config object
		$this->_config = new CArbitrageConfig($cpath, $env);

		//Require file
		if(file_exists($file))
		{
			$config = $this->_config;
			require_once($file);
		}
	}
}
?>
