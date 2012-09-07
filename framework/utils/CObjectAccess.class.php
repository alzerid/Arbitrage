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
		return $this->{$offset};
	}

	public function offsetGet($offset)
	{
		return $this->{$offset};
	}

	public function offsetSet($offset, $val)
	{
		$this->{$offset} = $val;
	}

	public function offsetUnset($offset)
	{
		unset($this->{$offset});
	}
	/* End ArrayAccess methods */

	/**
	 * Method traverses the values with Arbitrage Namespace Notation.
	 * @param string $path The arbitrage path.
	 * @return mixed Returns a value or a \Framework\Utils\CArrayObject
	 */
	public function apath($path)
	{
		//Get value from _variables
		$val = $this->_apath($path, $this->_variables);
		var_dump($val);
		die('CObjectAccess::apath');
		//return (($val === NULL)? $this->_apath($path, $this->_originals));
	}

	abstract protected function _getData($name);          //Used with __get magic method
	abstract protected function _setData($name, $val);    //Used with __set magic method
	abstract protected function _issetData($name);        //Used with the __isset magic method
	abstract protected function _unsetData($name);        //Used with the __unset magic method

	/**
	 * Method traverses the values with Arbitrage Namespace Notation.
	 * @param string $path The arbitrage path.
	 * @param array $arr The array to traverse.
	 * @return mixed Returns a value or a \Framework\Utils\CArrayObject
	 */
	private function _apath($path, array $arr)
	{
		//Get value
		$path = explode('.', $path);
		$key  = array_splice($path, 0, 1);
		$path = implode('.', $path);
		$key  = $key[0];
		$val  = ((!empty($arr[$key]))? $arr[$key] : NULL);

		//Recursive
		if(is_array($val))
			return $this->_apath($path, $val);

		return $val;
	}

}
?>
