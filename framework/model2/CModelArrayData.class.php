<?
namespace Arbitrage2\Model2;

class CModelArrayData extends \ArrayObject
{
	private $_type;

	public function __construct($type)
	{
		parent::__construct();
		$this->_type = $type;
	}

	public function __set($name, $val)
	{
		if(!is_object($val) || get_type($val) != $this->_type)
			throw new EModelData("Class type mismatch: Got '" . gettype($val) . "' expecting '{$this->_type}'");

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
		return $this->_type;
	}

	public function offsetSet($key, $val)
	{
		if(get_type($val) != $this->_type)
			throw new EModelDataExecption("Type mismatch. Expecting '{$this->_type}' got '" . get_type($val) . "'");

		parent::offsetSet($key, $val);
	}
}
?>
