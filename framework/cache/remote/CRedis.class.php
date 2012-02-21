<?
class CRedis implements IRemoteCache
{
	static private $_instance = NULL;
	private $_cache;

	public function __construct()
	{
		$this->_cache = new redis();
	}

	static public function getInstance()
	{
		if(self::$_isntance === NULL)
			self::$_instance = new CMemcache();

		return self::$_instance;
	}

	public function connect($host, $port)
	{
		$ret = @$this->_cache->connect($host, $port);
		if($ret === false)
			throw new EArbitrageRemoteCacheException("Unable to connect to memcache '$host:$port'.");
	}

	public function close()
	{
		$this->_cache->close();
	}

	public function get($key, $serialize=true)
	{
		return (($serialize)? unserialize($this->_cache->get($key)) : $this->_cache->get($key));
	}

	public function set($key, $value, $expire=0, $serialize=true, $flags=NULL)
	{
		$this->_cache->setex($key, $expire, (($serialize)? serialize($value) : $value));
	}

	public function add($key, $value, $expire=0, $serialize=true, $flags=NULL)
	{
		if($this->_cache->exists($key))
		{
			$this->_cache->setex($key, $expire,(($serialize)? serialize($value) : $value)); 
			return true;
		}

		return false;
	}

	public function delete($key, $flags=NULL)
	{
		$this->_cache->delete($key);
	}

	public function increment($key, $value=1, $expire=0)
	{
		return $this->_cache->inr($key, $value, $expire);
	}

	public function decrement($key, $value=1, $expire=0)
	{
		return $this->_cache->decrement($key, $value);
	}
}

?>
