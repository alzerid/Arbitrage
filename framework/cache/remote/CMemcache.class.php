<?
class CMemcache implements IRemoteCache
{
	static private $_instance = NULL;
	private $_cache;

	public function __construct()
	{
		$this->_cache = new Memcache();
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
		$this->_cache->set($key, (($serialize)? serialize($value) : $value), $flags, $expire);
	}

	public function add($key, $value, $expire=0, $serialize=true, $flags=NULL)
	{
		$this->_cache->add($key, (($serialize)? serialize($value) : $value), $flags, $expire);
	}

	public function delete($key, $flags=NULL)
	{
		$this->_cache->delete($key);
	}

	public function increment($key, $value=1, $expire=0)
	{
		$ret = $this->_cache->increment($key, $value);
		if($ret === false)
		{
			$this->_cache->set($key, $value, $expire);
			return $value;
		}

		return $ret;
	}

	public function decrement($key, $value=1, $expire=0)
	{
		$ret = $this->_cache->increment($key, -$value);
		if($ret === false)
		{
			$this->_cache->set($key, $value, $expire);
			return 0;
		}

		return $ret;
	}
}

?>
