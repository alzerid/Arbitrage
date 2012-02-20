<?
class Business
{
	static public function includeBusiness($controllerName)
	{
		global $_conf;	

		$path = "{$_conf['fsapipath']}$controllerName/business.php";
		if(!file_exists($path))
			Error::getInstance()->throwError("core", __FILE__, "Unable to find business class for API '$controllerName'.");

		require_once($path);
	}

	static public function includeCommon($filename)
	{
		global $_conf;

		$path = "{$_conf['fsfwpath']}lib/common/$filename.php";
		if(!file_exists($path))
			Error::getInstance()->throwError("core", __FILE__, "Unable to find class '$filename'.");

		require_once($path);
	}

	static public function includeLibrary($library)
	{
		global $_conf;

		$path = "{$_conf['fsfwpath']}lib/$library.php";
		if(!file_exists($path))
			Error::getInstance()->throwError("core", __FILE__, "Unable to find class '$library'.");

		require_once($path);
	}

	static public function getInstance($controllerName)
	{
		//Lazy load require
		Business::includeBusiness($controllerName);
		$class='b'.$controllerName;
		return new $class;
	}


	static public function execute($business_name, $method, $params)
	{
		$business = Business::getInstance($business_name);

		//Check if method exists
		if(!method_exists($business, $method))
			return array(1, "Invalid API call for '$business_name' call '$method'.");

		return call_user_func(array($business, $method), $params);
	}

	public function checkMethodExists($business, $method)
	{
		return method_exists($business, $method);
	}
}

class BusinessException extends Exception
{
	public function __construct($message, $code = 1)
	{
		parent::__construct($message, $code, NULL);
	}
}
?>
