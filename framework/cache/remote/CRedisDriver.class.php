<?
namespace Framework\Cache\Remote;

class CRedisDriver implements \Framework\Interfaces\IDriver, \Framework\Interfaces\IRemoteCache
{
	/**
	 * Class holds the handle and other information for the driver connection.
	 * @param $config The config to use for connection purposes.
	 */
	public function __construct($config)
	{
		$this->_config = $config;
		$this->_cache = new \Redis();
	}

	/**
	 * Method is called to connect to the remote cache server.
	 */
	public function connect()
	{
		$ret = @$this->_cache->connect($this->_config->host, $this->_config->port);
		if($ret === false)
			throw new EArbitrageRemoteCacheException("Unable to connect to redis server '$host:$port'.");
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

	public function leftPush($key, $value)
	{
		$ret = $this->_cache->lPush($key, $value);
		return (($ret > 0)? $ret : false);
	}

	public function leftPop($key)
	{
		return $this->_cache->lPop($key);
	}

	public function rightPush($key, $value)
	{
		$ret = $this->_cache->rPush($key, $value);
		return (($ret > 0)? $ret : false);
	}

	public function rightPop($key)
	{
		return $this->_cache->rPop($key);
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
