<?
namespace Framework\CLI;

class EArgumentException extends \Exception
{
}

interface IArgument { }

interface IArgumentCommand extends IArgument
{
	public function execute();
	public function setApplication(\Framework\Base\CCLIApplication $application);
	public function getCommand();
	public function getDescription();
	public function parse(array $args);
	public function help();
}

interface IArgumentCommandParent extends IArgumentCommand
{
	public function addChildCommand(\Framework\CLI\IArgumentCommand $command);
	public function getChildCommand($command);
	public function childCommandExists($command);
	public function childHelp();
}

interface IArgumentOption extends IArgument
{
	public function getLongOpt();
	public function getShortOpt();
	public function getValue();
	public function setValue($arg);
}

abstract class CArgumentBase implements IArgumentOption
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
 * Class used when commands are used for arguments instead of options.
 */
abstract class CArgumentCommand implements IArgumentCommand
{
	protected $_command;      //Command associated with this object
	protected $_description;  //Description of the command
	protected $_application;  //Application assigned to this command

	/**
	 * Constructor creates the object.
	 * @param $command The command to assign this object to.
	 */
	public function __construct($command, $description)
	{
		$this->_command     = $command;
		$this->_description = $description;
	}

	/**
	 * Method that returns the command associated to this IArgumentCommand class.
	 * @returns string The command associated to this class.
	 */
	public function getCommand()
	{
		return $this->_command;
	}

	/**
	 * Method returns the description of the command.
	 * @return string Returns the description.
	 */
	public function getDescription()
	{
		return $this->_description;
	}

	/**
	 * Parse the command.
	 * @param $args The argument list to parse out.
	 */
	public function parse(array $args)
	{
		$this->execute(implode(' ', $args));
	}

	/**
	 * Sets the application for this command object.
	 * @param \Framework\Base\CCLIApplication $application The application to set.
	 */
	public function setApplication(\Framework\Base\CCLIApplication $application)
	{
		$this->_application = $application;
	}
}

/**
 * Class used when commands are used for arguments instead of options.
 */
abstract class CArgumentCommandParent extends CArgumentCommand implements IArgumentCommandParent
{
	protected $_children;     //Child commands associated with this object

	/**
	 * Constructor creates the object.
	 * @param $command The command to assign this object to.
	 * @param $description The description of the command.
	 * @param $children The child commands associated with this object.
	 */
	public function __construct($command, $description, $children=array())
	{
		parent::__construct($command, $description);

		//Iterate and add
		$this->_children = array();
		foreach($children as $child)
			$this->_children[$child->getCommand()] = $child;
	}

	/**
	 * Parse the command.
	 * @param $args The argument list to parse out.
	 */
	public function parse(array $args)
	{
		//Check if $args[0] is in parent as a child
		if(isset($args[0]) && $this->childCommandExists($args[0]))
		{
			$command = $this->getChildCommand($args[0]);

			unset($args[0]);
			$args = array_values($args);
			$command->parse($args);
		}
		else
			parent::parse($args);
	}


	/**
	 * Method adds a child to the command object.
	 * @param \Framework\CLI\CArgumentCommand $command The command to add to this object.
	 */
	public function addChildCommand(\Framework\CLI\IArgumentCommand $command)
	{
		$this->_children[$command->getCommand()] = $command;
	}

	/**
	 * Get the child command,
	 * @return Returns the child command.
	 */
	public function getChildCommand($command)
	{
		return ((isset($this->_children[$command]))? $this->_children[$command] : NULL);
	}

	/**
	 * Checks if a child command exists within this object.
	 * @param $command The command to search for.
	 * @return Returns true if the command exists else false.
	 */
	public function childCommandExists($command)
	{
		return isset($this->_children[$command]);
	}

	/**
	 * Retuns child command information
	 * @return array Returns an array of commands and description.
	 */
	public function childHelp()
	{
		die('child help');
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
class CArgumentMultiple implements \ArrayAccess, \Iterator
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
				$this->_parseLongOpt($args, $key);
			elseif($key[0] == '-')               //Short option parse
				$this->_parseShortOpt($args, $key);
			else //Argument Command
			{
				//Check if key exists in $this->_args
				foreach($this->_args as $arg)
				{
					//Ensure argument is IArgumentCommand
					if(!$arg instanceof IArgumentCommand)
						continue;

					//Check to see if it exists
					if($arg->getCommand() == $key)
					{
						unset($args[0]);
						$args = array_values($args);
						$arg->parse($args);
						return;
					}
				}

				//Could not find any argument
				throw new EArgumentException("Unknown argument '$key'.");
			}
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
				if($arg instanceof CArgumentRequired)
				{
					$arg = $arg->getArgument();
					if($arg->getLongOpt() === $name)
						return $arg->getValue();
				}
				if($arg instanceof CArgumentMultiple)
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

	/**
	 * Method returns the argument list.
	 * @return array Returns the argument list.
	 */
	public function getArguments()
	{
		return $this->_args;
	}

	/**
	 * Method parses out a long option.
	 * @param $args The argument list.
	 * @param $key The key of the argument list.
	 */
	private function _parseLongOpt(&$args, $key)
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

	/**
	 * Method parses out a short option.
	 * @param $args The argument list.
	 * @param $key The key of the argument list.
	 */
	private function _parseShortOpt(&$args, $key)
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
}
?>
