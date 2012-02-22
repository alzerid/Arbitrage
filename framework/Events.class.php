<?
class CEvent
{
	private $_propagate = true;
	protected $_method  = NULL;

	public function stopPropagation()
	{
		$this->_propagate = false;
	}

	public function getPropagation()
	{
		return $this->_propagate;
	}

	public function triggerListeners(array $listeners)
	{
		if(count($listeners))
		{
			$method = $this->_method;
			foreach($listeners as $l)
			{
				$l->$method($this);
				if($this->_propagate === false)
					break;
			}
		}
	}
}

class CErrorEvent extends CEvent
{
	public $errno;
	public $errstr;
	public $message;
	public $file;
	public $line;
	public $trace;
	public $code;

	public function __construct($errno, $errstr, $message, $file, $line, $trace=NULL)
	{
		$this->_method = 'handleError';
		$this->errno   = $errno;
		$this->errstr  = $errstr;
		$this->message = $message;
		$this->file    = $file;
		$this->line    = $line;

		//TODO: Possibly remove first trace entry
		if($trace === NULL)
		{
			$trace = debug_backtrace();
			$trace = array_slice($trace, 2);
		}

		//Set trace
		$this->trace = $trace;
		$this->_getCode();
	}

	protected function _getCode()
	{
		foreach($this->trace as &$trace)
		{
			$line    = $trace['line'];
			$content = explode(PHP_EOL, file_get_contents($trace['file']));
			$cnt     = count($content);

			//Get range of code
			if($cnt > 10)
			{
				$start = $line - 5;
				$end   = $line + 5;

				//Normalize start and end
				if($start < 0)
					$start = 0;
				elseif($end >= $cnt)
					$end = $cnt-1;
			}

			//Get slice
			$content = array_slice($content, $start, $end-$start);

			$idx  = $start+1;
			$code = "";
			foreach($content as $c)
			{
				$class = (($idx == $line)? "selected" : "");
				$code .= '<div class="line ' . $class . '">' . ($idx) . '</div><div class="code ' . $class . '">' . $c . '</div><div class="clear"></div>';
				$idx++;
			}

			$trace['code'] = $code;
		}
	}
}

class CExceptionEvent extends CErrorEvent
{
	public $exception;

	public function __construct(Exception $ex)
	{
		$this->_method   = 'handeException';
		$this->exception = $ex;
		$this->errno     = $ex->getCode();
		$this->errstr    = "";
		$this->message   = $ex->getMessage();
		$this->file      = $ex->getFile();
		$this->line      = $ex->getLine();
		$this->trace     = $ex->getTrace();
		$this->_getCode();
	}
}
?>
