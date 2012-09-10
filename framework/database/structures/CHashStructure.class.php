<?
namespace Framework\Database\Structures;

//TODO: Code CLASS strictness when setting a CHashStructure with a class assigned to it --EMJ

class CHashStructure extends \Framework\Model\CMomentoModel implements \Framework\Interfaces\IDatabaseModelStructure, \Iterator
{
	private $_class;
	private $_keys;
	private $_idx;

	public function __construct()
	{
		$this->_class = NULL;
		parent::__construct();
	}

	/**
	 * Method instantiates the data type.
	 * @param $data The variables to set as default data for this Model.
	 * @param $class The class associated with the values.
	 */
	static public function instantiate($data=array(), $class=NULL)
	{
		//Instantiate
		$data = (($data===NULL)? array() : $data);
		$obj  = parent::instantiate($data);

		//Set class
		$obj->_class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($class);

		return $obj;
	}

	/**
	 * Method returns the updated query.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery()
	{
		die(__METHOD__);
		//TODO: Code smarter differences
		if(count($this->_data) == 0)
			return $this->_data;

		$ret = array_diff($this->_data, $this->_originals);
		if(count($ret) == 0)
			return NULL;

		return $this->_data;
	}

	/**
	 * Method clears the array back to it's original contents.
	 */
	public function clear()
	{
		die(__METHOD__);
		$this->_data = $this->_originals;
	}

	/*****************************/
	/** Iterator Implementation **/
	/*****************************/
	public function current()
	{
		$key = $this->_keys[$this->_idx];
		if(array_key_exists($key, $this->_variables))
			return $this->_variables[$key];

		return $this->_data[$key];
	}

	public function key()
	{
		return $this->_keys[$this->_idx];
	}

	public function next()
	{
		$this->_idx++;
	}

	public function rewind()
	{
		$this->_keys = array_merge(array_keys($this->_data), array_keys($this->_variables));
		$this->_idx  = 0;
	}

	public function valid()
	{
		return array_key_exists($this->_idx, $this->_keys);
	}
	/*********************************/
	/** End Iterator Implementation **/
	/*********************************/

	/**
	 * Method sets model data and converts special cases to objects.
	 * @param $data The data to set.
	 */
	protected function _setModelData($data)
	{
		foreach($data as $key=>$val)
		{
			if(is_array($val))
			{
				//Get class
				$class = $this->_class;
				if($class === NULL)
					throw new \Framework\Exceptions\EModelDataTypeException("Unknown class type!");

				//Create class
				$this->_data[$key] = $class::instantiate($val);
			}
			else
				$this->_data[$key] = $val;
		}
	}
}
?>
