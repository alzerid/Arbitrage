<?
class Component extends Application
{
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

	public function getSubArray($pre, $array)
	{
		$ret = array();
		foreach($array as $k=>$v)
		{
			$spos = strpos($k, $pre);
			if($spos !== false)
				$ret[substr($k, $spos+strlen($pre))] = $v;
		}

		return $ret;
	}
}
?>
