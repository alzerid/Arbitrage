<?
namespace Arbitrage2\Model2;

class CModelArrayData extends \ArrayObject
{
	private $_class;

	public function __construct($class)
	{
		parent::__construct();
		$this->_class = $class;
	}

	public function __set($name, $val)
	{
		if(!is_object($val) || get_class($val) != $this->_class)
			throw new EModelData("Class type mismatch: Got '" . gettype($val) . "' expecting '{$this->_class}'");

		$this[$name] = $val;
	}

	public function __get($name)
	{
		if(!array_key_exists($name, $this))
			throw new EModelDataException("Unknown $name");

		return $this[$name];
	}
	
	public function getClass()
	{
		return $this->_class;
	}

	public function offsetSet($key, $val)
	{
		if(!is_object($val) || get_class($val) != $this->_class)
			throw new EModelDataExecption("Class type mismatch");

		parent::offsetSet($key, $val);
	}
}
?>
