<?
class CDBFactory
{
	static private $_DB = array();

	static public function getDataBase($type, $db="_default")
	{
		if(isset(self::$_DB[$type]) && isset(self::$_DB[$type][$db]))
			return self::$_DB[$type][$db];

		$config = CApplication::getConfig();
		$config = $config->arbitrage->databases->$type->$db;

		if($config === NULL)
			throw new EArbitrageException("Application configuration does not contain database information for '$type' $db.");

		if(!isset(self::$_DB[$type]))
			self::$_DB[$type] = array();

		switch($type)
		{
			case 'mongo':
				$host = $config->host . ":" . $config->port;
				self::$_DB[$type][$db] = new Mongo($host);

				break;

			default:
				throw new EArbitrageException("Unknown database type '$type'.");
		}

		return self::$_DB[$type][$db];
	}
}
?>
