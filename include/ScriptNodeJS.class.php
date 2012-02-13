<?
define('TMP_DIR', '/tmp/arbitrage/scripts/nodejs/');

class ScriptNodeJS
{
	private $_class;
	private $_config;
	private $_script;
	private $_argv;

	public function __construct($script, $class, $argv)
	{
		$this->_class  = $class;
		$this->_config = json_encode(Application::getConfig()->getVariables()); //config to json
		$this->_script = $script;
		$this->_argv   = $argv;
	}

	public function run()
	{
		$dir = getcwd();
		chdir(dirname($this->_script));
		
		//Make sure arbitrage dir exists
		if(!file_exists(TMP_DIR))
			mkdir(TMP_DIR, 0777, true);

		$wrapper = Application::getConfig()->fwrootpath . "include/nodejs/ArbitrageApplication.js.wrapper";
		$content = file_get_contents($wrapper);
		$content = preg_replace('/{{CONFIG}}/', $this->_config, $content);
		$content = preg_replace('/{{APPLICATION}}/', file_get_contents($this->_script), $content);

		//Save to tmp dir
		$path = TMP_DIR . "/" . basename($this->_script);
		file_put_contents($path, $content);
		$exec    = "node $path";
		passthru($exec);

		chdir($dir);
	}
}
?>
