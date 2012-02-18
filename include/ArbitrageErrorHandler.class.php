<?
class ArbitrageErrorHandler extends Controller
{
	static $_PHP_ERROR_TYPE = array(
	  E_ERROR              => 'Error',
	  E_WARNING            => 'Warning',
	  E_PARSE              => 'Parsing Error',
	  E_NOTICE             => 'Notice',
	  E_CORE_ERROR         => 'Core Error',
	  E_CORE_WARNING       => 'Core Warning',
	  E_COMPILE_ERROR      => 'Compile Error',
	  E_COMPILE_WARNING    => 'Compile Warning',
	  E_USER_ERROR         => 'User Error',
	  E_USER_WARNING       => 'User Warning',
	  E_USER_NOTICE        => 'User Notice',
	  E_STRICT             => 'Runtime Notice',
	  E_RECOVERABLE_ERROR  => 'Catchable Fatal Error');

	static private $_exception = NULL;

	public function __construct($name="ArbitrageErrorHandler")
	{
		
	}

	public function showError()
	{
	}

	static public function addError($errno, $errstr, $errfile, $errline)
	{
		$prev = ((isset(self::$_exception))? self::$_exception : NULL);
		self::$_exception = new PHPException($errstr, $errno, $errfile, $errline, $prev);
	}

	static public function handleView()
	{
		if(self::$_exception === NULL)
			return;

		ob_end_clean();

		//Get error handler controller

		//Pretty print
		die("VIEW");
	}

	static public function handleHTMLFile()
	{
		if(self::$_exception === NULL)
			return;

		ob_end_clean();

		die("HTMLFILE");
	}

	//TODO: Create socket handling
	static public function handleSocket()
	{
		if(self::$_exception === NULL)
			return;

		ob_end_clean();
		die("SOCKET");
	}

	static private function _formatExceptions()
	{
		$exceptions = array();
	}
}
 
$mode = Application::getConfig()->arbitrage->errorHandler;
if($mode !== NULL)
{
	set_error_handler(array("ArbitrageErrorHandler", "addError"));
	register_shutdown_function(array('ArbitrageErrorHandler', "handle$mode"));
}
?>
