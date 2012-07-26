<?
namespace Framework\Base;
use \Arbitrage2\Config\CArbitrageConfig;
use \Arbitrage2\Config\CArbitrageConfigProperty;

class CRouter
{
	private $_routes;   //List of routes

	public function __construct($routes)
	{
		$this->_routes = $routes;
	}
	
	public function route($url)
	{
		if(isset($this->_routes))
		{
			foreach($this->_routes as $key=>$route)
			{
				$matches = array();
				if(preg_match($key, $url, $matches))
				{
					//String replace
					if(count($matches) > 1)
					{
						$replace = array_slice($matches, 1);
						foreach($replace as $pos => $val)
						{
							$idx    = ($pos+1);
							$search = '/(\\\|\$)' . $idx . '/';
							$route = preg_replace($search, ucfirst($val), $route);
						}
					}

					//Break from main loop cause we found our url
					$url = $route;
					break;
				}
			}
		}

		//Remove leading /
		$url = preg_replace('/^\//', '', $url);

		return $url;
	}
}
?>
