<?
class EArgumentException extends Exception
{
}

interface IArgument
{
	public function getLongOpt();
	public function getShortOpt();
	public function getValue();
	public function setValue($arg);
}

abstract class CArgumentBase implements IArgument
{
	protected $_value;

	private $_short;
	private $_long;

	public function __construct($short, $long)
	{
		$this->_short = $short;
		$this->_long  = $long;
		$this->_value = NULL;
	}

	public function getLongOpt()
	{
		return $this->_long;
	}

	public function getShortOpt()
	{
		return $this->_short;
	}

	public function getValue()
	{
		return $this->_value;
	}

	public function setValue($val)
	{
		$this->_value = $val;
	}
}

class CArgumentBoolean extends CArgumentBase
{
	public function __construct($short, $long, $default=false)
	{
		parent::__construct($short, $long);
		$this->_value = $default;
	}
}

class CArgumentValue extends CArgumentBase
{
	public function __construct($short, $long, $default=NULL)
	{
		parent::__construct($short, $long);
		$this->_value = $default;
	}
}

/**
 * Behavior class wrapper that makes an argument required.
 */
class CArgumentRequired
{
	private $_arg;

	public function __construct(CArgumentBase $arg)
	{
		$this->_arg = $arg;
	}

	public function __call($name, $args)
	{
		return call_user_func_array(array($this->_arg, $name), $args);
	}

	public function getArgument()
	{
		return $this->_arg;
	}
}

/**
 * Behavior class wrapper that acceps multiple arguments.
*/
class CArgumentMultiple implements ArrayAccess, Iterator
{
	private $_arg;
	private $_values;
	private $_idx;

	public function __construct(CArgumentBase $arg)
	{
		$this->_arg = $arg;
		$this->_idx = 0;
	}

	public function __call($name, $args)
	{
		return call_user_func_array(array($this->_arg, $name), $args);
	}

	public function getArgument()
	{
		return $this->_arg;
	}

	public function count()
	{
		return count($this->_values);
	}

	/* ArrayAccess Implementation */
	public function offsetExists($idx)
	{
		return isset($this->_values[$idx]);
	}

	public function offsetGet($idx)
	{
		return $this->_values[$idx];
	}

	public function offsetSet($idx, $val)
	{
		if($idx == "")
			$this->_values[] = $val;
		else
			$this->_values[$idx] = $val;
	}

	public function offsetUnset($idx)
	{
		unset($this->_values[$idx]);
	}
	/* End ArrayAccess Implementation */

	/* Iterator Implementation */
	public function current()
	{
		return $this->_values[$this->_idx];
	}

	public function key()
	{
		return $this->_idx;
	}

	public function next()
	{
		$this->_idx++;
	}

	public function rewind()
	{
		$this->_idx = 0;
	}

	public function valid()
	{
		return isset($this->_values[$this->_idx]);
	}
	/* End Iterator Implementation */
}

class CArgumentParser
{
	private $_args;

	public function __construct($args)
	{
		$this->_args = (($args === NULL)? array() : $args);
	}

	public function executeParse()
	{
		//TODO: Code --long=value
		global $argv;
		$args = array_slice($argv, 2);

		//Iterate through $argv and parse
		while(count($args))
		{
			$key = $args[0];
			if($key[0] == '-' && $key[1] == '-') //Long option parse
			{
				$key   = substr($key, 2);
				$found = false;
				foreach($this->_args as $arg)
				{
					if($arg->getLongOpt() === $key)
					{
						//Check rqeuired
						$multiple = NULL;
						if($arg instanceof CArgumentRequired)
							$arg = $arg->getArgument();
						elseif($arg instanceof CArgumentMultiple)
						{
							$multiple = $arg;
							$arg      = $arg->getArgument();
						}

						if($arg instanceof CArgumentBoolean)
						{
							if($multiple != NULL)
								$multiple[] = true;
							else
								$arg->setValue(true);

							unset($args[0]);
							$args  = array_values($args);
							$found = true;
						}
						elseif($arg instanceof CArgumentValue)
						{
							$opt = preg_replace('/\-\-/', '', $args[0]);
							if(count($args) < 2)
								throw new EArgumentException("Missing value for '{$opt}'.");

							if($multiple != NULL)
								$multiple[] = $args[1];
							else
								$arg->setValue($args[1]);

							unset($args[0]);
							unset($args[1]);
							$args  = array_values($args);
							$found = true;
						}
					}
				}

				//Key not found
				if(!$found)
					throw new EArgumentException("Unknown argument '$key'.");
			}
			elseif($key[0] == '-')               //Short option parse
			{
				$key = substr($key, 1);

				//Find short opt
				foreach($this->_args as $arg)
				{
					if($arg->getShortOpt() === $key)
					{
						if($arg instanceof CArgumentBoolean)
							$arg->setValue(true);
						elseif($arg instanceof CArgumentValue)// || $arg instanceof CArgumentMultiValue)
						{
							//TODO: Extract value
							die('value short');
							//$val 
							//$arg->setValue(
						}
					}
				}
			}
			else  //Show invalid command
				throw new EArgumentException("Unknown argument '$key'.");
		}

		//Go through and make sure all required args have been set
		foreach($this->_args as $arg)
		{
			if($arg instanceof CArgumentRequired && $arg->getValue() === NULL)
			{
				$lopt = $arg->getLongOpt();
				$sopt = $arg->getShortOpt();
				$opt  = "";

				if($lopt !== NULL)
					$opt .= "--$lopt";

				if($sopt !== NULL)
				{
					$sopt = "-$sopt";

					if(strlen($opt))
						$opt .= ", $sopt";
					else
						$opt = $sopt;
				}

				throw new EArgumentException("Required argument '{$opt}' is missing.");
			}
		}
	}

	public function __get($name)
	{
		foreach($this->_args as $arg)
		{
			//Short OPT
			if(strlen($name) == 1)
			{
				die("SHORT OPT");
			}
			else
			{
				if($arg instanceof CArgumentRequired || $arg instanceof CArgumentMultiple)
				{
					$type = $arg;
					$arg  = $arg->getArgument();

					if($arg->getLongOpt() === $name)
						return $type;
				}
				else
				{
					if($arg->getLongOpt() === $name)
						return $arg->getValue();
				}
			}
		}

		return NULL;
	}
}
?>
