<?
class CCLIApplication extends CApplication
{
	static private $_EXT = array("php" => "PHP", 'js' => 'NodeJS', 'py' => 'Python', 'bin' => 'Native');
	private $_app;

	protected function __construct()
	{
		$this->_app = NULL;
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
		$this->_app = $this->loadApplication($argv[1]);

		//Run application
		$this->_app->process();
	}

	public function loadApplication($script)
	{
		$extarr = implode(',', array_keys(self::$_EXT));
		$path   = $this->getConfig()->_internals->approotpath . "app/scripts/$script/$script.{" . $extarr . "}";
		$glob   = glob($path, GLOB_BRACE);
		
		if(count($glob) != 1)
			throw new EArbitrageException("Unable to load script '$script'.");

		//require file
		$file = explode(".", basename($glob[0]));
		$file = $file[0];
		$ext  = $file[1];
		require_once($glob[0]);

		$class = ucwords($file) . "Application";
		$obj   = new $class;

		return $obj;
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
			echo $this->_app->getApplicationName() . ": " . $event->exception->getMessage() ."\n\n";
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
