<?
namespace Framework\Database\Types;

class CModelHashData extends \Framework\Database\Types\CModelData implements \Iterator
{
	private $_class;          //Class associated with the hash values
	private $_position;       //Iterator position
	private $_keys;           //Iterator keys
	
	public function __construct($class=NULL)
	{
		//TODO: Code for non ModelData class
		//TODO: Code for basic value

		$class            = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($class);
	 	$this->_class     = $class;
		$this->_originals = array();
		$this->_variables = array();

		//Generate types etc...
		if($class !== NULL)
		{
			$class::_generateTypes($class, $class::defaults());

			//Originals
			if($this->_originals === NULL)
				throw new \Framework\Exceptions\EModelDataException("Unable to get default values for '" . get_called_class() . "'.");
		}
	}

	/* Start Iterator Implementation */
	public function current()
	{
		$key = $this->_keys[$this->_position];
		$val = ((array_key_exists($key, $this->_variables))? $this->_variables[$key] : $this->_originals[$key]);
		
		return $val;
	}

	public function key()
	{
		return $this->_keys[$this->_position];
	}

	public function next()
	{
		++$this->_position;
	}

	public function rewind()
	{
		$this->_keys     = array_keys($this->_originals);
		$this->_position = 0;
	}

	public function valid()
	{
		return isset($this->_keys[$this->_position]);
	}
	/* End Iterator Implementation */


	protected function _setData($name, $val)
	{
		var_dump($name, $val);
		die("CModelHashData::_setData");
		if($val instanceof CModelData)
			$this->_variables[$name] = $val;
		else
			throw new \Framework\Database\Exceptions\EModelDataException('Unable to directly set CModelHashData that is not of type ' . $this->_class);
	}

	protected function _getData($name)
	{
		//TODO: Do i need to clone hashes that are of type CModelData ??? CModelData should handle iteself... --EMJ

		//Check to see if we have something in variables
		if(array_key_exists($name, $this->_variables))
			return $this->_variables[$name];

		//If we got one in originals, clone it, set it into _variables and return
		if(array_key_exists($name, $this->_originals))
		{
			$this->_variables[$name] = (($this->_class)? clone $this->_originals[$name] : $this->_originals[$name]);
			return $this->_variables[$name];
		}

		//Create a whole new object from _class
		$this->_variables[$name] = (($this->_class)? new $this->_class : "");

		return $this->_variables[$name];
	}

	/**
	 * Method sets originals for a model.
	 * @param $originals The original variables.
	 */
	protected function _setModelData(array &$originals=array(), $class=NULL)
	{
		$class = $this->_class;
		if($class)
		{
			foreach($originals as $key=>$val)
			{
				$this->_originals[$key] = new $this->_class();
				$this->_originals[$key]->_setModelData($val);
			}
		}
		else
		{
			$this->_originals = $originals;
			die('CModelHashData::_setModelData no class associated');
		}
	}

	protected function _merge()
	{
		//Move _variables that are unset in _originals to _orignals
		foreach($this->_originals as $key=>$val)
			$val->_merge();
	}
}
?>
