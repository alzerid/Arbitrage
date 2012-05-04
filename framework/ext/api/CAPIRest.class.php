<?
class CAPIRest extends CAPI
{
	static protected $_INSTANCE;
	
	protected $_modules;
	protected $_module;
	protected $_parent;
	protected $_curl;
	protected $_url;

	public function __construct(CAPIRest $parent=NULL, $module=NULL)
	{
		$this->_modules   = (($parent===NULL)? static::$_MODULES : $parent->_modules[$module]);
		$this->_module    = $module;
		$this->_parent    = $parent;
		$this->_curl      = Curl::getInstance();
		$this->_url       = (($parent===NULL)? static::$_URL : "{$parent->_url}/{$module}");
	}

	static public function getInstance()
	{
		if(static::$_INSTANCE === NULL)
		{
			$class = get_called_class();
			self::$_INSTANCE = new $class();
		}

		return self::$_INSTANCE;
	}

	static public function checkModule($name)
	{
		return isset(static::$_MODULES[$name]);
	}

	public function __get($name)
	{
		$name = strtolower($name);
		if(!static::checkModule($name))
			throw new EAPIException("Invalid module '$name'");

		$class = get_called_class();
		return new $class($this, $name);
	}

	public function __call($name, $arguments)
	{
		//Check for self call
		$url = $this->_url;
		if(in_array($name, $this->_modules) || $this->_module === NULL && in_array(':self', $this->_modules[$name]))
			$url .= "/$name";
		else
			throw new EAPIException("Unknown method '$name' for module '{$this->_module}'.");

		//Append arguments
		$url .= "?" . http_build_query($arguments[0]);

		return $this->_curl->get($url);
	}
}
?>
