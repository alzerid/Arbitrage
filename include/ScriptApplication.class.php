<?
abstract class ScriptApplication extends Application
{
	public function __construct()
	{
		global $argv;

		$_GET  = $argv;
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

	abstract public function run();
	abstract public function help();

	//Parse arguments

}
?>
