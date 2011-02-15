<?
class Component
{
	protected $_get;
	protected $_post;
	protected $_cookie;
	protected $_session;

	protected $_controller_name;
	protected $_action_name;

	public function __construct()
	{
		$this->_get = $_GET;
		unset($this->_get['_route']);

		$this->_post     = $_POST;
		$this->_cookie   = $_COOKIE; 
		
		if(isset($_SESSION))
			$this->_session =& $_SESSION;
		else
			$this->_session = NULL;
	}

	static function initComponents()
	{
		global $_components;
		global $_conf;

		//Load up components
		$_components = array();
		$comps = glob($_conf['approotpath'] . "components/*.php");
		foreach($comps as $comp)
		{
			$name = basename($comp);
			$name = substr($name, 0, -4);
			require_once($comp);
			$_components[$name] = new $name;

		}
	}

	public function redirect($redirect)
	{
		$url = new URL($redirect);
		header("Location: " . $url->getURL());
		die();
	}

	public function generateLink($tag, $url, $attribs = NULL)
	{
		if(is_string($url) && strstr($url, '/'))
		{
			$a = "";
			if($attribs != NULL)
			{
				foreach($attribs as $k=>$v)
					$a .= " $k=\"$v\"";
			}

			$href = "<a href=\"/$url\" $a>$tag</a>";
			return $href;
		}
		elseif(is_array($url))
		{
			$url = $url[0];
			if(strstr($url, '/'))
			{
				$a = "";
				if($attribs != NULL)
				{
					foreach($attribs as $k=>$v)
						$a .= " $k=\"$v\"";
				}

				$href = "<a href=\"/$url\" $a>$tag</a>";
				return $href;
			}

		}

		return "NEED TO CODE GENERATE LINK";
	}
}
?>
