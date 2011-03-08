<?
abstract class Module extends Application
{
	protected $_module_name;
	protected $_options;

	public function __construct($name)
	{
		$this->_module_name = $name;
	}

	abstract function process();

	public function renderPartial($view, $_vars=NULL)
	{
		global $_conf;

		//Check if view is set
		$view_path = $_conf['approotpath'] . "modules/{$this->_module_name}/views/{$view}.php";

		if(isset($_vars) && is_array($_vars))
			extract($_vars);

		ob_start();
		require_once($view_path);
		$content = ob_get_clean();

		return $content;
	}

	public function render($_vars=NULL)
	{
		global $_conf;

		//Check if view is set
		$view_path = $_conf['approotpath'] . "modules/{$this->_module_name}/views/{$this->_module_name}.php";

		if(isset($_vars) && is_array($_vars))
			extract($_vars);

		ob_start();
		require_once($view_path);
		$content = ob_get_clean();

		return $content;
	}

	public function setOptions($options)
	{
		$this->_options = $options;
	}

	public function includeJavascript($file)
	{
		self::includeJavascriptFile("/modules/{$this->_module_name}/public/javascript/$file");
	}

	public function includeStylesheet($file)
	{
		self::includeStylesheetFile("/modules/{$this->_module_name}/public/stylesheets/$file");
	}
}
?>
