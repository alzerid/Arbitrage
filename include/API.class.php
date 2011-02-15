<?
class API
{
  protected $return_type;
  protected $rm;            //Return Medium class
  protected $params;
  private $call;
	private $api;
  private $route;

  public function __construct($call)
  {
    //Setup object variables
    $this->call   = $call;
    $this->params = $_GET;
    $this->route  = $_GET['_route_api'];
		$this->api    = dirname($this->route);

    //Remove application level paramaters
    unset($this->params['_return_type']);
    unset($this->params['_route_api']);
		unset($this->params['_help']);

    //Setup return type
    $return_type = (isset($_GET['_return_type'])? $_GET['_return_type'] : 'json');

    //Create the return medium
    $this->rm = new ReturnMedium($return_type);
  }


	private function parameterCheck()
	{
		//Get YAML definitions
		$params = $this->params;
		$yaml   = $this->getDefinitions();

		//Make sure the call is even valid
		if(!isset($yaml[$this->api]['methods'][$this->call]))
		{
			$this->rm->parse(array(1, "API call '{$this->call}' is not defined."));
			return false;
		}

		$call   = $yaml[$this->api]['methods'][$this->call];
		$req    = $call['required_arguments'];
		$opt    = (($call['optional_arguments'] == NULL)? array(): $call['optional_arguments']);

		//Remove optional from parameter list
		if(count($opt))
		{
			foreach($opt as $k=>$v)
			{
				if(array_key_exists($k, $params))
					unset($params[$k]);
			}
		}

		//Check for empty parameter list
		if(count($req) != 0 && count($params) == 0)
		{
			$this->rm->parse(array(1, "API call '{$this->call}' takes arguments."));
			return false;
		}

		//Check if there are arguments that should not be there and we are NOT in strict mode (default mode)
		if(!isset($call['params']) || (isset($call['params']) && $call['params'] == "strict"))
		{
			foreach($params as $k=>$v)
			{
				if(!array_key_exists($k, $req) && !array_key_exists($k, $opt))
				{
					$this->rm->parse(array(1, "API call '{$this->call}' does not take '$k' as an argument."));
					return false;
				}
			}
		}

		//Check if required_arguments are there
		if(count($req))
		{
			foreach($req as $k=>$v)
			{
				if(!array_key_exists($k, $params))
				{
					$this->rm->parse(array(1, "Missing required argument '$k' for api call '{$this->call}'."));
					return false;
				}
			}
		}

		return true;
	}

	private function getDefinitions()
	{
		global $_conf;

		//Check if the definitions exist in memory
		$lcache = LocalCacheFactory::initLocalCache('apc');
		$key    = $this->call . '_definitions';
		//if(!$lcache->get($key))
		{
			//Load up YAML definitions file
			$path = "{$_conf['fsapipath']}{$this->api}/definitions.yaml";
			if(!file_exists($path))
				Error::getInstance()->throwError('core', __FILE__, "Unable to find definitions file for '$api'.");

			$yaml = yaml_parse(file_get_contents($path));
			//$lcache->set($key, $definitions, 600);
		}

		return $yaml;
	}

	private function displayHelp()
	{
		global $_conf;

		Business::includeLibrary('templating/Template.class');
		$template  = new Template($_conf['fsrootpath']. "/public");
		$yaml = $this->getDefinitions();
		$template->render('doc', array('yaml' => $yaml, '_title' => 'API Doc'));
		die();
	}

  protected function includeBusiness($controllerName)
  {
		Business::includeBusiness($controllerName);
  }

  public function execute()
  {
		/* Order of operations:
		 * Check if API module exists
		 * Check for method in Business class, if none existant throw error
		 * Method defined in API class, call that.
		 * Method defined in Business class, call that.
		 */
		
		//Check if call is defined and do parameter checks
		if(isset($_GET['_help']))
		{
			$this->displayHelp();
			die();
		}


		//Figure out where we will be calling the api, in the API wrapper or business class
		$class  = "";
		$method = "";
		if(method_exists($this, $this->call)) //api wrapper
		{
			$class  = $this;
			$method = $this->call;
		}
		else //business
		{
			$class  = Business::getInstance($this->api);
			$method = $this->call;
		}

		//TODO: Error if the actual API module does not exist

		//Check if method exists
		if(method_exists($class, $method))
		{
			try
			{
				//Check parameter list
				if($this->parameterCheck())
				{
					$ret = $class->$method($this->params);
					$this->rm->parse($ret);
				}
			}
			catch(Exception $e)
			{
				$this->rm->parse(array(1, $e->getMessage()));
			}
		}
		else
			$this->rm->parse(array(1, "API call '{$this->call}' does not exist."));

		//Show return
    $this->rm->display();
  }
}
?>
