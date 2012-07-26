<?
namespace Framework\Database;

class CMongoDriver extends CDatabaseDriver
{
	static private $_HANDLES = array();

	static public function getHandle($config)
	{
		//Get key
		$key = "mongodb://" . ((isset($config['host']))? $config['host'] : '127.0.0.1') . ':' . ((isset($config['port']))? $config['port'] : 27017);

		//Get handle
		if(isset(self::$_HANDLES[$key]))
			return self::$_HANDLES[$key];

		//Create new mongo handle
		self::$_HANDLES[$key] = new \Mongo($key);

		return self::$_HANDLES[$key];
	}
}
?>
