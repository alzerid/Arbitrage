<?
namespace Framework\Cache\Remote;

class CMemcacheDriver implements \Framework\Interfaces\IDriver, \Framework\Interfaces\IRemoteCache
{
	static private $_instance = NULL;
	private $_cache;
	private $_config;

	public function __construct($config)
	{
		$this->_config = $config;
		$this->_cache  = new \Memcache();
	}

	public function connect()
	{
		$host = $this->_config->host;
		$port = $this->_config->port;

		$ret = @$this->_cache->connect($host, $port);
		if($ret === false)
			throw new \Framework\Exceptions\EArbitrageRemoteCacheException("Unable to connect to memcache '$host:$port'.");
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

	public function leftPush($key, $value)
	{
		die("MEMCACHE: leftPush");
	}

	public function leftPop($key)
	{
		die("MEMCACHE: leftPop");
	}

	public function rightPush($key, $value)
	{
		die("MEMCACHE: rightPush");
	}

	public function rightPop($key)
	{
		die("MEMCACHE: rightPop");
	}

	/***********************/
	/** IDriver Interface **/
	/***********************/

	/*
	 * Method returns the raw handle of the driver.
	 * @return Returns the handle.
	 */
	public function getHandle()
	{
		return $this->_handle;
	}

	/**
	 * Method retuns the configuration of this driver.
	 * @returns array Returns driver configuration.
	 */
	public function getConfig()
	{
		return $this->_config;
	}
}
?>
