<?
class Error
{
	static private $_self = NULL;

	public function __construct()
	{
	}

	static public function getInstance()
	{
		if(Error::$_self == NULL)
			Error::$_self = new Error();

		return Error::$_self;
	}

	public function throwError($type, $file, $err, $die=true)
	{
		FastLog::logit($type, $file, $err);
		echo "$err";
		if($die)
			die();
	}
}
?>
