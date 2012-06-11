<?
class CErrorHandler implements IObserver, ISingleton
{
	static private $_instance = NULL;
	static private $_PHP_ERROR_TYPE = array(
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

	private $_observer;

	public function __construct()
	{
		$this->_listeners = array();
	}

	static public function getInstance()
	{
		if(self::$_instance === NULL)
			self::$_instance = new CErrorHandler();

		return self::$_instance;
	}

	/* IListener Implementation */
	public function prependListener(IListener $listener)
	{
		$this->_listeners = array_merge(array($listener), $this->_listeners);
		return true;
	}

	public function addListener(IListener $listener)
	{
		$this->_listeners[] = $listener;
		return true;
	}

	public function clearListeners()
	{
		$this->_listeners = array();
	}

	public function removeListener(IListener $listener)
	{
		$cnt = count($this->_listeners);
		for($i=0; $i<$cnt; $i++)
		{
			if($this->_listeners[$i] == $listener)
			{
				unset($this->_listeners[$i]);
				$this->_listeners = array_values($this->_listeners);
				return true;
			}
		}

		return false;
	}
	/* END IListener Implementation */

	/**
	 * Static function handles all PHP errors.
	 * @param $errno The integer value of the PHP error.
	 * @param $errstr The error string.
	 * @param $errfile The file the PHP error occurred in.
	 * @param $errline The line where the PHP error occurred in.
	 */
	static public function handleError($errno, $errstr, $errfile, $errline)
	{
		//TODO: Check for stop propagation from event
		$event = new CErrorEvent($errno, self::$_PHP_ERROR_TYPE[$errno], $errstr, $errfile, $errline);

		//Send event to handleError listeners
		$listeners = CErrorHandler::getInstance()->_listeners;
		$return    = $event->triggerListeners($listeners);

		return (($return == NULL)? false : $return);
	}

	/**
	 * Static function handles exceptions that are not caught
	 * in a try catch block.
	 * @param $ex The exception that was thrown.
	 */
	static public function handleException(Exception $ex)
	{
		//TODO: Check for stop propagation from event

		$event = new CExceptionEvent($ex);

		//Send event to the listeners
		$listeners = CErrorHandler::getInstance()->_listeners;
		$event->triggerListeners($listeners);
	}
}

//Set global exception handler and php error handler
set_error_handler(array("CErrorHandler", "handleError"));
set_exception_handler(array("CErrorHandler", "handleException"));
?>
