<?
namespace Framework\Base;
use \Framework\Interfaces\ISingleton;
use \Framework\Exceptions\EArbitrageKernelException;

//TODO: Ability to have multiple applications --EMJ
//TODO: Ability to manage package_paths per application --EMJ
//TODO: Ability for applications to have their own virtual namespaces (manage their own pacakges etc, ensure service objects are tied to application) --EMJ

class CKernel implements ISingleton
{
	static private $_VERION     = "2.0.0";
	static protected $_INSTANCE = NULL;
	
	private $_package_paths;           //List of paths where packages are supposed to be searched for
	private $_service_paths;           //List of paths where services are supposed to be searched for
	private $_applications;            //List of applications managed by the Kernel
	private $_services;                //List of services managed by the Kernel
	private $_path;                    //The path of where the framework exists

	private $_bootstrap_web;           //Boolean indicating we included web specific files
	private $_bootstrap_cli;           //Boolean indicating we included cli specific files

	protected function __construct()
	{
		$this->_package_paths = array();
		$this->_service_paths = array();
		$this->_applications  = array();
		$this->_services      = array();
		$this->_path          = '';

		$this->_bootstrap_web = false;
		$this->_bootstrap_cli = false;
	}

	static public function getInstance()
	{
		if(self::$_INSTANCE == NULL)
			self::$_INSTANCE = new CKernel;

		return self::$_INSTANCE;
	}

	/**
	 * Setups up the kernel through a bootstrap process.
	 */
	public function bootstrap()
	{
		//Ensure ARBITRAGE2_FW_PATH exists
		if(!file_exists(ARBITRAGE2_FW_PATH))
			die("Framework path is not defined in environment variable ARBITRAGE2_FW_PATH");

		//Set path
		$this->_path            = ARBITRAGE2_FW_PATH;
		$this->_service_paths[] = $this->_path;
		$this->_package_paths[] = $this->_path;

		//Require needed files
		$ret = $this->requireFrameworkFile('Exceptions', false);                    //File full of base exception classes
		if(!$ret)
			die("Unable to include Exceptions file!");

		$this->requireFrameworkFile('Events');                               //Events
		$this->requireFrameworkFile('ErrorHandler.CErrorHandlerObserver');   //Error Handler Observer
		$this->requireFrameworkFile('Utils.CArrayObject');                   //Array object used by most Arbitrage classes
		$this->requireFrameworkFile('Utils.CObjectAccess');                  //Array object used by most Arbitrage classes
		$this->requireFrameworkFile('Base.CService');                        //Base service class
		$this->requireFrameworkFile('Base.CServiceContainer');               //Service container class
		$this->requireFrameworkFile('Base.CExtension');                      //Extension class
		$this->requireFrameworkFile('Base.CPackage');                        //Package class
		$this->requireFrameworkFile('Base.CApplication');                    //Application class
		$this->requireFrameworkFile('Config.CArbitrageConfig');              //Configuration object
		$this->requireFrameworkFile('Config.CArbitrageConfigLoader');        //Configuration loader
	}

	/**
	 * Requires a file by using namespace resolution.
	 * @param $namespace The namespace to convert to a file name.
	 * @param $opt_throw Optional parameter that specifies if we should throw an error.
	 * @param $opt_variables Optional parameter that pases variables into the required file.
	 * @throws \Framework\Exceptions\EArbitrageKernelException
	 * @return boolean Returns true if the file was included else false.
	 */
	public function requireFile($namespace, $opt_throw=true, $opt_variables=array())
	{
		//Call private method
		$ret = $this->_requireFile($namespace, $opt_variables);

		//Check if we should throw an exception
		if($opt_throw && $ret === NULL)
			throw new EArbitrageKernelException("Unable to require '$namespace'.");

		return ($ret != NULL);
	}

	/**
	 * Method requires a framework file.
	 * @param string $namespace The Arbitrage namespace to include.
	 * @param $opt_throw Optional parameter that specifies if we should throw an error.
	 * @param $opt_variables Optional parameter that pases variables into the required file.
	 * @throws \Framework\Exceptions\EArbitrageKernelException
	 * @return boolean Returns true if the file was included else false.
	 */
	public function requireFrameworkFile($namespace, $opt_throw=true, $opt_variables=array())
	{
		$namespace = "Framework.$namespace";
		$path      = $this->_path . "/" . $this->convertArbitrageNamespaceToPath($namespace). ".class.php";

		//Extract variables
		if(count($opt_variables))
			extract($opt_variables);

		//Check if exists
		if(!file_exists($path))
		{
			if($opt_throw)
				throw new EArbitrageKernelException("Unable to require '$namespace' ($path).");

			return false;
		}

		//Require the file
		require_once($path);

		return true;
	}

	/**
	 * Adds a path to the package search path.
	 * @param sring $path The path to add for the application search path.
	 * @throws \Framework\Exceptions\EArbitrageKernelException
	 */
	public function registerPackagePath($path)
	{
		$this->_package_paths[] = $path;
		$this->_pacakge_paths   = array_unique($this->_package_paths);
	}

	/**
	 * Method returns the registered package paths.
	 * @return array The regsitered package paths.
	 */
	public function getPackagePaths()
	{
		return $this->_package_paths;
	}

	/**
	 * Returns the framework filesystem path.
	 * @returns string Returns the absolute filesystem path to the framework.
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Adds a service path when searching for services.
	 * param string $path The path to register in the search service paths.
	 */
	public function registerServicePath($path)
	{
		$this->_service_paths[] = $path;
	}

	/**
	 * Method returns the registered service paths.
	 * @return array The regsitered service paths.
	 */
	public function getServicePaths()
	{
		return $this->_service_paths;
	}

	/**
	 * Creates a package and returns it.
	 * @param string $namespace The namespace the package resides on.
	 * @param \Framework\Base\CPackage $opt_parent The parent package this package will belong to.
	 * @param \Framework\Config\CArbitrageConfig $opt_config The local configuration for this package.
	 * @return \Framework\Base\CApplication Returns a web application.
	 */
	public function createPackage($namespace, \Framework\Base\CPackage $opt_parent=NULL, \Framework\Config\CArbitrageConfigProperty $opt_config=NULL)
	{
		//Add actual package name to namespace
		if(strpos($namespace, '.') === false)
			$namespace = "$namespace.C{$namespace}Package";
		else
			$namespace = preg_replace('/(.*)\.([^\,]+)$/', '$1.$2.C$2Package', $namespace);

		//Create the package
		$package = $this->_createPackage($namespace, $opt_parent, $opt_config);
		if($package == NULL)
			throw new EArbitrageKernelException("Application '$namespace' does not exist!");

		//Initialize package
		$package->initialize();
		return $package;
	}

	/**
	 * Creates a Web application.
	 * @namespace The root namespace the application resides on.
	 * @return \Framework\Base\CApplication Returns a web application.
	 */
	public function createWebApplication($namespace)
	{
		//Ensure we bootstrap web applications
		$this->_bootstrapWebApplication();

		//Get class 
		$class = $this->convertArbitrageNamespaceToPHP($namespace);
		$info  = $this->_requireFile(preg_replace('/\.[^\.]+$/', '.application', $namespace));

		//Ensure this class is of type CApplication
		if(!is_subclass_of($class, '\Framework\Base\CApplication'))
			throw new EArbitrageKernelException("Application '$namespace' does not extend CApplication!");

		//Create application
		$application = new $class($info['path'], $info['namespace']);
		$application->initialize();

		//Add application to applications list
		$this->_applications[] = $application;

		return $application;
	}

	/**
	 * Creates a CLI Application.
	 * @param string $namespace The namespace the application resides in.
	 * @return \Framework\Base\CCLIApplication Returns a CLI application.
	 */
	public function createCLIApplication($namespace)
	{
		//Ensure we bootstrap web applications
		$this->_bootstrapCLIApplication();

		//Get class 
		$class = $this->convertArbitrageNamespaceToPHP($namespace);
		$info  = $this->_requireFile(preg_replace('/\.[^\.]+$/', '.application', $namespace));

		//Ensure this class is of type CApplication
		if(!is_subclass_of($class, '\Framework\Base\CApplication'))
			throw new EArbitrageKernelException("Application '$namespace' does not extend CApplication!");

		//Create application
		$application = new $class($info['path'], $info['namespace']);
		$application->initialize();

		//Add application to applications list
		$this->_applications[] = $application;

		return $application;
	}

	/**
	 * Initialize services via configuration object.
	 * @param \Framework\Base\CApplication $application The application object.
	 */
	public function initializeServices(\Framework\Base\CApplication $application)
	{
		//Get arbitrage2 config
		$config = $application->getConfig();
		if($config->arbitrage2 && $config->arbitrage2->services)
		{
			$services = $config->arbitrage2->services;
			foreach($services as $service => $value)
			{
				foreach($value as $namespace => $lconfig)
					$this->createService($application, $service, $namespace, \Framework\Config\CArbitrageConfigProperty::instantiate($lconfig));  //Create service with configuration
			}
		}
	}

	/**
	 * Method returns a CService.
	 * @param \Framework\Base\CApplication $application The application object.
	 * @param string $service_type The type of service to fetch.
	 * @return \Framework\Base\CService Returns a CService instance or NULL.
	 */
	public function getService($application, $service_type)
	{
		if(!isset($this->_services[$service_type]))
			return NULL;

		return $this->_services[$service_type]->getService($application);
	}

	/**
	 * Creates a service and regsisters it into the kernel.
	 * @param \Framework\Base\CApplication $application The application object.
	 * @param string $service The service namespace to register to.
	 * @param string $namespace The namespace to load.
	 * @param \Framework\Config\CArbitrageConfig $config Configuration object to tie to the serivce.
	 */
	public function createService($application, $service, $namespace, \Framework\Config\CArbitrageConfigProperty $config)
	{
		//TODO: Ensure service is not already loaded

		//Require service
		$file = $this->convertArbitrageNamespaceToPath($namespace) . ".class.php";
		foreach($this->_service_paths as $path)
		{
			$path .= $file;
			if(file_exists($path))
			{
				require_once($path);

				//TODO: Load service in global space but only provide them to applications that request it.
				
				//Create Service
				$class = $this->convertArbitrageNamespaceToPHP($namespace);
				if(!class_exists($class))
					throw new EArbitrageKernelException("Service '$namespace' does not exist!");

				//Create a new service container
				if(!isset($this->_services[$service]))
					$this->_services[$service] = new CServiceContainer($service);

				//Add application/config to service container
				$this->_services[$service]->registerApplicationToService($application, dirname($path), $class, $config);

				return;
			}
		}

		throw new EArbitrageKernelException("Unable to load '$namespace' for service '$service'.");
	}

	/**
	 * Method returns the applications.
	 * returns \Framework\Base\CApplication Retuns the application registered to the kernel.
	 */
	public function getApplication()
	{
		return ((isset($this->_applications[0]))? $this->_applications[0] : NULL);
	}

	/**
	 * Converts an Arbitrage namespace to the equivalent path.
	 * @param $namespace The namespace to conver to a path.
	 * @return Returns the path from the namespace.
	*/
	public function convertArbitrageNamespaceToPath($namespace)
	{
		//Seperate file from the rest
		$path = explode('.', $namespace);
		$file = array_splice($path, -1);

		//Normalize path
		$path = implode('/', $path);
		$path = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $path);
		$path = preg_replace('/ /', '_', $path);
		$path = strtolower(preg_replace('/\./', DIRECTORY_SEPARATOR, $path));

		//Normalize File
		$file = $file[0];

		return $path . DIRECTORY_SEPARATOR . $file;
	}

	/**
	 * Method converts an Arbitrage namespace string to the equivalent PHP namespace.
	 * @param string $namespace The namespace to convert.
	 * @return Returns the PHP namespace.
	 */
	public function convertArbitrageNamespaceToPHP($namespace)
	{
		$namespace = preg_replace('/\./', '\\', $namespace);
		return "\\$namespace";
	}

	/**
	 * Method converts a PHP namespace to the equivalent Arbitrage namespace.
	 * @param string $namespace The PHP namesace to convert.
	 * @return string Returns the Arbitrage namespace.
	 */
	public function convertPHPNamespaceToArbitrage($namespace)
	{
		$namespace = preg_replace('/\\\/', '.', $namespace);
		$namespace = preg_replace('/^\./', '', $namespace);
		return $namespace;
	}

	/**
	 * Method converts a URL formatted string to an arbitrage namespace.
	 * @param string $url The URL to convert to an arbitrage namespace.
	 * @return Returns an arbitrage namespace.
	 */
	public function convertURLNamespaceToArbitrage($url)
	{
		$namespace = ucwords(preg_replace('/\//', ' ', $url));
		$namespace = preg_replace('/ /', '.', $namespace);
		$namespace = ucwords(preg_replace('/_/', ' ', $namespace));
		$namespace = preg_replace('/ /', '', $namespace);

		return $namespace;
	}

	/**
	 * Method converts an arbitrage formatted namespace to a url.
	 * @params string $namespace The namespace to convert.
	 * @return string Returns the url.
	 */
	public function convertArbitrageNamespaceToURL($namespace)
	{
		$url = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $namespace);
		$url = strtolower(preg_replace('/\s/', '_', $url));
		$url = preg_replace('/\./', '/', $url);

		return $url;
	}

	/**
	 * Methods requires a file.
	 * @param $namespace The namespace to convert to a file name.
	 * @param $opt_variables Optional parameter that pases variables into the required file.
	 * @return array Returns path of the package and namespace. Returns NULL if file does not exist.
	 */
	private function _requireFile($namespace, $opt_variables=array())
	{
		$ret = NULL;

		//Iterate through and find file
		$file = $this->convertArbitrageNamespaceToPath($namespace);

		//Extract opt_variables
		if(count($opt_variables))
			extract($opt_variables);

		//Find the file in _package_paths
		foreach($this->_package_paths as $path)
		{
			$opath = $path;
			$path .= "/$file";
			if(file_exists("$path.php"))
			{
				require_once("$path.php");
				$ret = array('path' => $opath, 'namespace' => preg_replace('/\.[^\.]+$/', '', $namespace));
				break;
			}
			elseif(file_exists("$path.class.php"))
			{
				require_once("$path.class.php");
				$ret = array('path' => $opath, 'namespace' => preg_replace('/\.[^\.]+$/', '', $namespace));
				break;
			}
		}

		return $ret;
	}

	/**
	 * Method to create a package
	 * @param string $namespace The namespace the package resides on.
	 * @param \Framework\Base\CPackage $opt_parent The parent package this package will belong to.
	 * @param \Framework\Config\CArbitrageConfig $opt_config The local configuration for this package.
	 * @return \Framework\Base\CApplication Returns a web application.
	 */
	private function _createPackage($namespace, \Framework\Base\CPackage $opt_parent=NULL, \Framework\Config\CArbitrageConfigProperty $opt_config=NULL)
	{
		//Get class
		$class = $this->convertArbitrageNamespaceToPHP($namespace);
		$info  = $this->_requireFile($namespace);

		//Ensure class exists
		if(!class_exists($class))
			return NULL;

		//Create new application
		$package = new $class($info['path'], $info['namespace'], $opt_parent, $opt_config);
		return $package;
	}

	/**
	 * Method includes WEB Application files.
	 */
	public function _bootstrapWebApplication()
	{
		if(!$this->_bootstrap_web)
		{
			$this->requireFrameworkFile('Base.CWebApplication');                 //Web application class
			$this->_bootstrap_web = true;
		}
	}

	/**
	 * Method includes CLI APplicatino files.
	 */
	public function _bootstrapCLIApplication()
	{
		if(!$this->_bootstrap_cli)
		{
			$this->requireFrameworkFile('CLI.CCommand');
			$this->requireFrameworkFile('CLI.CArgumentParser');
			$this->requireFrameworkFile('Base.CCLIApplication');                 //Web application class
			$this->_bootstrap_cli = true;
		}
	}
}
?>
