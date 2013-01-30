<?php
namespace Framework\Database2\Model\Structures;

//TODO: code get magic methods
//todo: code set magic methods
//TODO: Use _variables and _data appropriately

class CArray extends \Framework\Database2\Model\Structures\CStructure implements \Countable, \Iterator
{
	protected $_class;
	private $_idx;

	public function __construct($class=NULL, $data=array())
	{
		$this->_class = $class;
		$this->_idx   = -1;

		//TODO: Call CModel
		\Framework\Model\CModel::__construct($data);
		$this->_variables = array();
	}

	/**
	 * Method returns the class associated with the entries in the array.
	 * @return Returns the class associated with the entries in the array.
	 */
	public function getClass()
	{
		return $this->_class;
	}

	/** 
	 * Method merges the variables array into the data array.
	 */
	public function merge()
	{
		//TODO: We will need to wensure _variables is merged as well
		if(count($this->_variables))
			throw new \Framework\Exceptions\ENotImplementedException("Merging of _variables not yet implemented.");

		//Iterate through each object (if there is a class associated with it)
		foreach($this->_data as $data)
		{
			if($data instanceof \Framework\Database2\Model\CModel)
				$data->merge();
		}
	}

	/**
	 * Method sets data to the variables array
	 */
	public function set($data)
	{
		//TODO: Handle array

		//Handle CArray
		if($data instanceof \Framework\Database2\Model\Structures\CArray)
		{
			//TODO: Set _data and _variables
			$this->_data      = $data->_data;
			$this->_variables = $data->_variables;
		}
		else
			throw new \Framework\Exceptions\ENotImplementedException("Unable to handle data type.");
	}

	/**************************************/
	/** Start ArrayAccess Implementation **/
	/**************************************/

	/**
	 * Method checks if an entry exists in the offset.
	 * @return boolean Returns true if the offest exists else false.
	 */
	public function offsetExists($offset)
	{
		die(__METHOD__);
	}

	/**
	 * Method checks returns an entry within the array.
	 * @return Returns an entry in the array.
	 */
	public function offsetGet($offset)
	{
		if(!isset($this->_data[$offset]))
			return NULL;

		return $this->_data[$offset];
	}

	/**
	 * Method sets an entry into the offset.
	 * @param $offset The offset to set the entry to.
	 * @param $value The value set to the offset.
	 */
	protected function _offsetSet($offset, $value)
	{
		if($offset === NULL)
			$offset = count($this->_data);

		$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($this->_class);
		if($this->_class && !($value instanceof $class))
			throw new \Framework\Exceptions\EDatabaseDataTypeException("Value must be instanceof '{$this->_class}'.");

		$this->_data[$offset] = $value;
	}

	/**
	 * Method unsets and entry within the array.
	 * @param $offset The offset to unset.
	 */
	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
		$this->_data = array_values($this->_data);
	}

	/************************************/
	/** End ArrayAccess Implementation **/
	/************************************/

	/************************************/
	/** Start Countable Implementation **/
	/************************************/

	public function count()
	{
		return count($this->_data);
	}

	/**********************************/
	/** End Countable Implementation **/
	/**********************************/

	/***********************************/
	/** Start Iterator Implementation **/
	/***********************************/
	public function current()
	{
		return $this->_data[$this->_idx];
	}

	public function next()
	{
		$this->_idx++;
	}

	public function key()
	{
		return $this->_idx;
	}

	public function valid()
	{
		return array_key_exists($this->_idx, $this->_data);
	}

	public function rewind()
	{
		$this->_idx = 0;
	}

	/*********************************/
	/** End Iterator Implementation **/
	/*********************************/

	/**
	 * Method searches for the needle in the array.
	 * @param $needle The needle to search for.
	 * \return Returns true if the needle is in the array.
	 */
	public function contains($needle)
	{
		if($this->_variables && in_array($needle, $this->_variables))
			return true;

		return in_array($needle, $this->_data);
	}
}
?>
