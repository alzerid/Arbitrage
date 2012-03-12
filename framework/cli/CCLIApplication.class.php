<?
class CCLIApplication extends CApplication
{
	static private $_EXT = array("php" => "PHP", 'js' => 'NodeJS', 'py' => 'Python', 'bin' => 'Native');
	protected $_cache;
	private $_app;

	protected function __construct()
	{
		parent::__construct();
		$this->_app   = NULL;
		$this->_cache = NULL;

		//Change to current working project directory
		chdir(dirname($_SERVER['PHP_SELF']));
	}

	/**
	 * Loads all the required files for the framework.
	 */
	public function bootstrap()
	{
		parent::bootstrap();

		//Include CLI classes
		$this->requireFrameworkFile('cli/CArgumentParser.class.php');
		$this->requireFrameworkFile('cli/CCLIBaseApplication.class.php');
		$this->requireFrameworkFile('cli/CPHPApplication.class.php');
	}

	/**
	 * Run the script.
	 */
	public function run()
	{
		global $argv;

		//TODO: Determine script type, php, python, nodejs etc
		if(count($argv) < 2)
			$this->help();

		//Get script to run
		$this->_app = $this->loadApplication(strtolower($argv[1]));

		//Create PID
		$this->createPID();

		//Run application
		$this->_app->process();

		//Remove PID
		$this->removePID();
	}

	public function getCacheObject()
	{
		return $this->_cache;
	}

	public function loadApplication($script)
	{
		$extarr = implode(',', array_keys(self::$_EXT));
		$path   = $this->getConfig()->_internals->approotpath . "app/scripts/$script/$script.{" . $extarr . "}";
		$glob   = glob($path, GLOB_BRACE);
		
		if(count($glob) != 1)
			throw new EArbitrageException("Unable to load script '$script'.");

		//Create temporary cache
		if($this->_cache === NULL)
		{
			$app    = basename(CApplication::getConfig()->_internals->approotpath);
			$script = basename(CApplication::getConfig()->_internals->scriptrootpath);
			$path   = "$app/$script/";

			$this->_cache = new CTemporaryCache($path);
		}

		//require file
		$file = explode(".", basename($glob[0]));
		$file = $file[0];
		$ext  = $file[1];
		require_once($glob[0]);

		//Add internal config variable
		CArbitrageConfig::getInstance()->_internals->scriptrootpath = dirname($glob[0]) . "/";

		//Create new object
		$class = ucwords($file) . "Application";
		$obj   = new $class;

		return $obj;
	}

	public function createPID($pid='pid')
	{
		$this->_cache->putContent("pid/$pid", getmypid());
	}

	public function removePID($pid='pid')
	{
		$this->_cache->delete("pid/$pid");
	}

	public function checkPID($pid = 'pid')
	{
		//Get PID
		$pid = $this->_cache->getContent("pid/$pid");
		if($pid === NULL || $pid === "")
			return false;

		//Check to see if PID actually exists and is runnin
		$pid = trim($pid);
		if(file_exists("/proc/$pid"))
			return true;

		return false;
	}

	public function ensureOneInstance($pid='pid')
	{
		//Get PID
		$pid = $this->_cache->getContent($pid);
		if($pid === NULL)
			return;

		//Check to see if PID actually exists and is runnin
		$pid = trim($pid);
		if($this->checkPID($pid))
			throw new EArbitrageException("Application currently exists in user space.");
		else
			$this->removePID();
	}

	public function help()
	{
		$extarr = implode(',', array_keys(self::$_EXT));

		printf("./script.php <program> [arguments]\n\n");
		printf("%-20s %-10s %-15s\n", "Script", "Type", "Details");

		//Glob application script directory
		$globdir = self::getConfig()->_internals->approotpath . "app/scripts/*";
		$globdir = glob($globdir, GLOB_ONLYDIR);
		foreach($globdir as $dir)
		{
			$file     = basename($dir);
			$filedir  = $dir . "/" . $file . ".{" . $extarr . "}";
			$fileglob = glob($filedir, GLOB_BRACE);

			if(count($fileglob))
			{
				$file = explode('.', basename($fileglob[0]));
				$obj  = $this->loadApplication($file[0]);
				$desc = $obj->getApplicationDescription();
				$type = $obj->getApplicationType();
				$name = $obj->getApplicationName();

				printf("%-20s %-10s %s\n", $name, $type, $desc);
			}

			echo "\n";
		}

		die();
	}

	/* IErrorHandlerListener  Methods */
	public function handleError(CErrorEvent $event)
	{
		echo "Arbitrage2 Error Handler\n";
		echo str_repeat('-', 30) . "\n";
		echo "{$event->errstr}({$event->errno}): {$event->message}\n";
		echo "{$event->file}:{$event->line}\n\n";
		die();
	}

	public function handleException(CExceptionEvent $event)
	{
		if($event->exception instanceof EArgumentException)
		{
			echo $this->_app->getApplicationName() . ": " . $event->exception->getMessage() ."\n\n";
			$this->_app->help();
		}
		else
		{
			echo "Arbitrage2 Exception Handler\n";
			echo str_repeat('-', 30) . "\n";
			echo $event->exception->getMessage() . "\n";
			echo $event->exception->getFile() . ":" . $event->exception->getLine() . "\n\n";
		}

		die();
	}
	/* End Exception Listner Methods */

}
?>
