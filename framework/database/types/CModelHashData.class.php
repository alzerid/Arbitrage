<?
namespace Framework\Database\Types;

class CModelHashData extends CModelData
{
	private $_class;
	
	public function __construct($class)
	{
		parent::__construct();
		$this->_class = \Framework\Base\CKernel::getInstance()->convertArbitrageNameToPHP($class);
	}

	protected function _setData($name, $val)
	{
		if($val instanceof CModelData)
			$this->_variables[$name] = $val;
		else
			throw new \Framework\Database\Exceptions\EModelDataException('Unable to directly set CModelHashData that is not of type ' . $this->_class);
	}

	protected function _getData($name)
	{
		//Check to see if we have something in variables
		if(array_key_exists($name, $this->_variables))
			return $this->_variables[$name];

		//If we got one in originals, clone it, set it into _variables and return
		if(array_key_exists($name, $this->_originals))
		{
			$this->_variables[$name] = clone $this->_originals[$name];
			return $this->_variables[$name];
		}

		//Create a whole new object from _class
		$this->_variables[$name] = new $this->_class;

		return $this->_variables[$name];
	}

	protected function _merge()
	{
		//Move _variables that are unset in _originals to _orignals
		foreach($this->_originals as $key=>$val)
			$val->_merge();
	}
}
?>
