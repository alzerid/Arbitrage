<?php
namespace Framework\CLI\ArgumentParser;

/**
 * Behavior class wrapper that makes an argument required.
 */
class CRequiredArgument
{
	private $_arg;

	public function __construct(\Framework\CLI\ArgumentParser\CBaseArgument $arg)
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

