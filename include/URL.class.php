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
			$newurl = $url[0];
			if(strstr($newurl, '/'))
			{
				$ret = explode('/', $newurl);
				$this->_controller_name = $ret[0];
				$this->_action_name     = $ret[1];
				$this->_url = "/$newurl";
			}

			//Add params
			if(isset($url[1]))
			{
				$params = "?";
				foreach($url[1] as $k=>$v)
					$params .= "$k=" . urlencode($v) . "&";

				$params = substr($params, 0, -1);
				$this->_url .= $params;
			}
		}
		else
		{
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
