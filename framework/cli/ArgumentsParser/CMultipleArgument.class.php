<?php
namespace Framework\CLI\ArgumentParser;

/**
 * Behavior class wrapper that acceps multiple arguments.
*/
class CMultipleArgument implements \ArrayAccess, \Iterator
{
	private $_arg;
	private $_values;
	private $_idx;

	public function __construct(CBaseArgument $arg)
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

?>
