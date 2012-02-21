<?
class CRemoteCacheFactory implements IFactory
{
	static private $_caches = array();

	static public function get($type)
	{
		if(isset(self::$_caches[$type]))
			return $_caches[$type];

		return NULL;
	}

	static public function initialize($caches)
	{
		//TODO: Error checking to check if file exists
		foreach($caches as $key=>$value)
		{
			$class = "C" . ucwords($key);

			CApplication::getInstance()->requireFrameworkFile("cache/remote/$class.class.php");
			$cache = new $class();
			$cache->connect($value->host, $value->port);

			//Connect
			self::addCacheHandler($key, $cache);
		}
	}

	static public function addCacheHandler($type, $obj)
	{
		self::$_caches[$type] = $obj;
	}
}
?>
