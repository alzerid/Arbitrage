<?
namespace Arbitrage2\Base;
use \Arbitrage2\Config\CArbitrageConfig;

class CPackage
{
	private $_path;       //The absolute filesystem path the package resides
	private $_namespace;  //Root namespace describing this package
	private $_config;     //The configuration object
	private $_parent;     //The parent package that loaded this package

	/**
	 * Constructor initializes the CPackace instance.
	 * @param string $path The path where the package resides in.
	 * @param string $namespace The namespace associated with the object.
	 * @param \Arbitrage2\Base\CPackage $parent The parent of this package.
	 * @param \Arbitrage2\Config\CArbitrageConfigProperty $config The configuration to merge with.
	 */
	public function __construct($path, $namespace, $parent=NULL, \Arbitrage2\Config\CArbitrageConfigProperty $config=NULL)
	{
		$this->_path      = $path;
		$this->_namespace = $namespace;
		$this->_config    = $config;
		$this->_parent    = $parent;
	}

	/**
	 * Initializes the package.
	 */
	public function initialize()
	{
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
	 * Method gets the root parent.
	 * @return \CArbitrage\Base\CPackage Returns the parent most package.
	 */
	public function getRootParent()
	{
		$current = $this;
		do
		{
			$prev    = $current;
			$current = $prev->getParent();
		} while($current);

		return $prev;
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
	 * Method returns the namespace of this package.
	 * @return string Returns the namespace of this package.
	 */
	public function getNamespace()
	{
		return $this->_namespace;
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

		//Require file
		if(file_exists($file))
		{
			$old_config = $this->_config;
			$this->_config = new CArbitrageConfig($cpath, $env);
			$config = $this->_config;
			require_once($file);

			//Merge old and new config
			if($old_config)
			{
				var_dump($old_config);
				$this->_config->merge($old_config);
			}
		}
	}
}
?>
