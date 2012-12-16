<?php
namespace Framework\CLI\ArgumentParser;

class CArgumentParser
{
	private $_args;

	/**
	 * Argument Parser constructor.
	 * Default arguments.
	 */
	public function __construct($args=array())
	{
		$this->_args = $args;
	}

	/**
	 * Method adds an argument into the arguments list.
	 * @param \Framework\CLI\CArgumentBase $arg Argument to add to the argument list.
	 */
	public function addArgument(\Framework\CLI\CArgumentBase $arg)
	{
		$this->_args[] = $arg;
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
