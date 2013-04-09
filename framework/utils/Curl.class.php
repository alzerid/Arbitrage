<?php
namespace Framework\Utils;

class Curl
{
	static private $_instance;
	private $_myCurl;
	private $_cookie;

	public function __construct($timeout)
	{
		$this->_myCurl = curl_init();
		$this->_cookie = NULL;
		curl_setopt($this->_myCurl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_myCurl, CURLOPT_TIMEOUT, $timeout);
	}

	public function __destruct()
	{
		curl_close($this->_myCurl);
		Curl::$_instance == NULL;
	}

	public static function getInstance($timeout=30)
	{
		if(Curl::$_instance == NULL)
			Curl::$_instance = new Curl($timeout);

		//Set timeout
		curl_setopt(Curl::$_instance->_myCurl, CURLOPT_TIMEOUT, $timeout);

		return Curl::$_instance;
	}

	public function setTimeOut($timeout)
	{
		curl_setopt($this->_myCurl, CURLOPT_TIMEOUT, $timeout);
	}

	public function setOption($opt, $val)
	{
		curl_setopt($this->_myCurl, $opt, $val);
	}

	public function generateCookie()
	{
		$cookie = tempnam("/tmp", "cookie");
		$this->setCookie($cookie);

		return $this->_cookie;
	}

	public function setCookie($cookie)
	{
		$this->_cookie = $cookie;
		curl_setopt($this->_myCurl, CURLOPT_COOKIEJAR, $this->_cookie);
		curl_setopt($this->_myCurl, CURLOPT_COOKIEFILE, $this->_cookie);
	}

	public function setProxy($proxy)
	{
		curl_setopt($this->_myCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
		curl_setopt($this->_myCurl, CURLOPT_PROXY, $proxy);
	}

	public function getCookie()
	{
		return $this->_cookie;
	}

	public function getInfo($code)
	{
		return curl_getinfo($this->_myCurl, $code);
	}

	public function get($url, $keepalive = false)
	{
		try
		{
			if($keepalive == true)
				curl_setopt($this->_myCurl, CURLOPT_HTTPHEADER, array('Connection: Keep-Alive', 'Keep-Alive: 300'));

			curl_setopt($this->_myCurl, CURLOPT_POST, 0);
			curl_setopt($this->_myCurl, CURLOPT_URL, $url);
			$this->myData = curl_exec($this->_myCurl);

			if($this->myData === false || curl_errno($this->_myCurl))
				throw new Exception("Could not connect to '$url' " . curl_errno($this->_myCurl));
		}
		catch(Exception $e)
		{
			throw $e;
		}

		return $this->myData;
	}

	public function post($url, $fields)
	{
		//If fields is an array, convert to string
		if(is_array($fields))
		{
			$params = "";
			foreach($fields as $k=>$v)
				$params .= "$k=$v&";

			$fields = substr($params, 0, -1); 
		}

		try
		{
			curl_setopt($this->_myCurl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->_myCurl, CURLOPT_POST, 1);
			curl_setopt($this->_myCurl, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($this->_myCurl, CURLOPT_URL, $url);
			$this->myData = curl_exec($this->_myCurl);

			if($this->myData === false || curl_errno($this->_myCurl))
				throw new \Exception("Could not connect to '$url' " . curl_errno($this->_myCurl));
		}
		catch(Exception $e)
		{
			throw $e;
		}
		return $this->myData;
	}

	public function getUrl($url, $type)
	{
		try
		{
			if (!function_exists("curl_init") && !function_exists("curl_setopt") && !function_exists("curl_exec") && !function_exists("curl_close"))
				throw new Exception('cURL Functions do not exist - Check to see if the Module is installed');

			curl_setopt($this->_myCurl, CURLOPT_URL, $url);

			$this->myData = curl_exec($this->_myCurl);

			if($this->myData === false || curl_errno($this->_myCurl))
				throw new Exception("Could not connect to '$url' " . curl_errno($this->_myCurl));

			if($type == "JSON")
			{
				$json = json_decode($this->myData);

				if ($json->{'header'}->{'message'} == 'Success')
				{
					#Returns the json of the actual curl for processing on a per module basis
					return $json;
				}
				else
				{
					throw new Exception('Could not Read Response from Server');
				}
			}
			elseif($type == "text")
				return $this -> myData;
			else
				throw new Exception('No valid type defined');
		}
		catch (Exception $e)
		{
			trigger_error('cURL Failed - ' . $e->getMessage(), E_USER_ERROR);
		}
	}

	public function getFile($url, $file, $flag)
	{
		//Open file
		$fh = fopen($file, $flag);

		//Set option
		$this->setOption(CURLOPT_FILE, $fh);
		$this->get($url);

		fclose($fh);
	}
}
?>
