<?
abstract class ScriptApplication extends Application
{
	static private $_LOADING = array("|", "/", "-", "\\");
	static private $_LIDX    = 0;

	public function __construct()
	{
		global $argv;

		$_GET  = array_slice($argv, 2);
		$_POST = $_GET;

		parent::__construct();
	}

	static public function getClassName($script)
	{
		$class  = str_replace('_', ' ', $script);
		$class  = ucwords($class);
		$class  = str_replace(' ', '', $class);
		$class  = preg_replace('/\.(php|js)$/', '', $class);
		$class .= "Script";

		return $class;
	}

	static public function scriptInclude($filename)
	{
		$config = Application::getConfig();
		$file   = "{$config->scriptrootpath}/$filename";
		if(!file_exists($file))
			throw new ArbitrageException("Unable to include controller '$filename'.");

		require_once($file);
	}

	static public function loadingPrint($message)
	{
		echo $message . self::$_LOADING[self::$_LIDX] . "\r";
		self::$_LIDX = (++self::$_LIDX % count(self::$_LOADING));
	}

	static public function getApplicationObject($app, $argv)
	{
		//Choose proper application wrapper
		$path = Application::getConfig()->approotpath . "app/scripts/$app/$app";
		Application::getConfig()->setVariable('scriptrootpath', dirname($path) . "/");

		if(file_exists("$path.php"))
		{
			//Require application
			require_once("$path.php");

			//Create an instance and run the script
			$class  = ScriptApplication::getClassName($app);
			$script = new $class($argv);
		}
		elseif(file_exists("$path.js"))
		{
			//Require Node JS Wrapper
			require_once(Application::getConfig()->fwrootpath . "include/ScriptNodeJS.class.php");

			//Make sure required nodeJS is available
			if(!file_exists("/usr/bin/node"))
			{
				echo "Cannot run Javascript app because nodejs does not exist in /usr/bin/node!\n";
				die();
			}

			//Setup class
			$class  = ScriptApplication::getClassName($app);
			$script = new ScriptNodeJS($path . ".js", $class, $argv);
		}
		else
		{
			echo "Unable to find application script $app.\n";
			die();
		}

		return $script;
	}

	abstract public function run();
	abstract public function help();

	protected function _traverseArray(&$arr, $cb)
	{
		foreach($arr as $key=>&$val)
		{
			if(is_array($val))
				$this->_traverseArray($arr[$key], $cb);
			else
				$cb($key, $val);
		}
	}
}
?>
