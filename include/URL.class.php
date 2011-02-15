<?
class URL
{
	public $_controller_name;
	public $_action_name;
	public $_url;

	public function __construct($url)
	{
		$this->normalize($url);
	}

	public function getURL()
	{
		return $this->_url;
	}

	public function normalize($url)
	{
		if(is_array($url))
		{
			$url = $url[0];
			if(strstr($url, '/'))
			{
				$ret = explode('/', $url);
				$this->_controller_name = $ret[0];
				$this->_action_name     = $ret[1];
				$this->_url = "/$url";
			}
		}
		else
		{
			echo "URL CLASS doesnt understand code me!!!";
			$this->_url = $url;
		}
	}

	public function getController()
	{
		return $this->_controller;
	}

	public function getAction()
	{
		return $this->_action;
	}
}
?>
