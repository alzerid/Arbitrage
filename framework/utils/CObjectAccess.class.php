<?
namespace Framework\Utils;

abstract class CObjectAccess implements \ArrayAccess, \Framework\Interfaces\IAPath
{
	/** Object Access Pattern **/
	public function __get($name)
	{
		return $this->_getData($name);
	}

	public function __set($name, $val)
	{
		$this->_setData($name, $val);
	}

	public function __isset($name)
	{
		return $this->_issetData($name);
	}

	public function __unset($name)
	{
		$this->_unsetData($name);
	}
	/** End Object Acecss Pattern **/

	/* ArrayAccess methods */
	public function offsetExists($offset)
	{
		return $this->_offsetExists($offset);
	}

	public function offsetGet($offset)
	{
		return $this->_offsetGet($offset);
	}

	public function offsetSet($offset, $val)
	{
		$this->_offsetSet($offset, $val);
	}

	public function offsetUnset($offset)
	{
		$this->_offsetUnset($offset);
	}

	//These are needed for method override patterns
	protected function _offsetExists($offset)
	{
		return $this->{$offset};
	}

	protected function _offsetGet($offset)
	{
		return $this->{$offset};
	}

	protected function _offsetSet($offset, $val)
	{
		$this->{$offset} = $val;
	}

	protected function _offsetUnset($offset)
	{
		unset($this->{$offset});
	}
	/* End ArrayAccess methods */

	abstract protected function _getData($name);          //Used with __get magic method
	abstract protected function _setData($name, $val);    //Used with __set magic method
	abstract protected function _issetData($name);        //Used with the __isset magic method
	abstract protected function _unsetData($name);        //Used with the __unset magic method
}
?>
