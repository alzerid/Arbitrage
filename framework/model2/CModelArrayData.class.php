<?
namespace Arbitrage2\Model2;

class CModelArrayData extends CModelData implements \Iterator
{
	private $_unset;
	private $_iterate;
	private $_idx;

	public function __construct($defaults = array())
	{
		$this->_originals = $defaults;
		$this->_variables = array();
		$this->_unset     = array();
		$this->_path      = array();
		$this->_iterate   = array();
		$this->_idx       = 0;
	}

	public function reset()
	{
		$this->_unset = array();
		parent::reset();
	}

	public function toArray()
	{
		$vars = $this->_originals;

		//If unset is set, remove from oritinals
		foreach($this->_unset as $key=>$val)
			unset($vars[$key]);

		$vars = array_values(array_merge($vars, $this->_variables));
		return $vars;
	}

	public function toArrayUpdated()
	{
		return $this->toArray();
	}

	/* Iterator methods */
	public function current()
	{
		return $this->_iterate[$this->_idx];
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
		$this->_iterate = $this->toArray();
		$this->_idx     = 0; //real index
	}

	public function valid()
	{
		return array_key_exists($this->_idx, $this->_iterate);
	}
	/* End Iterator methods */

	public function contains($val)
	{
		return (in_array($val, $this->_originals) || in_array($val, $this->_variables));
	}

	protected function _getData($idx)
	{
		if(array_key_exists($idx, $this->_variables))
			return $this->_variables[$idx];
		elseif(array_key_exists($idx, $this->_originals))
			return $this->_originals[$idx];

		return NULL;
	}

	protected function _setData($idx, $val)
	{
		//Ensure $val is primitive and not a CModelData or Mongo*
		$type = gettype($val);
		if($type == "object")
		{
			$class = get_class($val);
			if(!preg_match('/^mongo/i', $class) || !($val instanceof CModelData))
				throw new EModelDataException("Value must be a primitive type, Mongo* class, or CModelData type");
		}

		if($idx === "")
		{
			$idx = count($this->_originals) + count($this->_variables);
			$this->_variables[$idx] = $val;
		}
		elseif(array_key_exists($idx, $this->_originals) || array_key_exists($idx, $this->_variables))
			$this->_variables[$idx] = $val;
		else
			throw new EModelDataException("Unknown index $idx.");
	}

	protected function _issetData($idx)
	{
		return (!isset($this->_unset[$idx]) && (array_key_exists($idx, $this->_variables) || array_key_exists($idx, $this->_originals)));
	}

	protected function _unsetData($idx)
	{
		if(array_key_exists($idx, $this->_variables))
			unset($this->_variables[$idx]);

		if(array_key_exists($idx, $this->_originals))
			$this->_unset[(int) $idx] = true;
	}

	protected function _setModelData(array &$originals=array())
	{
		$this->_originals = $originals;
	}

	protected function _merge()
	{
		$originals = $this->_originals;

		//If unset is set, remove from oritinals
		foreach($this->_unset as $key=>$val)
			unset($originals[$key]);

		$originals        = array_values(array_merge($originals, $this->_variables));
		$this->_originals = $this->toArray();

		$this->reset();
	}

}
?>
