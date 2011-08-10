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
		$class  = preg_replace('/\.php$/', '', $class);
		$class .= "Script";

		return $class;
	}

	static public function scriptInclude($filename)
	{
		$config = Application::getConfig();
		$file   = "{$config->scriptrootpath}/$filename";
		if(!file_exists($file))
			throw new CocaineException("Unable to include controller '$filename'.");

		require_once($file);
	}

	static public function loadingPrint($message)
	{
		echo $message . self::$_LOADING[self::$_LIDX] . "\r";
		self::$_LIDX = (++self::$_LIDX % count(self::$_LOADING));
	}

	abstract public function run();
	abstract public function help();



	//Parse arguments

}
?>
