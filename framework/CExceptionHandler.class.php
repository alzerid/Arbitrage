<?
//Class handles exceptions and PHP errors
class CExceptionHandler implements IObserver, ISingleton
{
	static private $_instance = NULL;
	private $_listeners;

	public function __construct()
	{
		$this->_listeners = array();

		//Register global exception handler
		set_exception_handler(array($this, "handleException"));
	}

	public static function getInstance()
	{
		if(self::$_instance === NULL)
			self::$_instance = new CExceptionHandler();

		return self::$_instance;
	}

	public function handleException(Exception $ex)
	{
		foreach($this->_listeners as $listener)
			$listener->handleException($ex);
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

	public function triggerListeners()
	{
		/*foreach($this->_listeners as $l)
			$l->handleException(*/
	}
}
?>
