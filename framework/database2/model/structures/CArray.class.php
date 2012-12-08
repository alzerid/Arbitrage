<?php
namespace Framework\Database2\Model\Structures;

//TODO: code get magic methods
//todo: code set magic methods

class CArray extends \Framework\Model\CMomentoModel implements \ArrayAccess, \Countable/*, \Iterator*/
{
	private $_class;

	public function __construct($class=NULL, $data=array())
	{
		$this->_class = $class;

		//TODO: Call CModel
		\Framework\Model\CModel::__construct($data);
		$this->_variables = array();
	}

	/** 
	 * Method merges the variables array into the data array.
	 */
	public function merge()
	{
		die(__METHOD__);
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
		die(__METHOD__);
	}

	/**
	 * Method sets an entry into the offset.
	 * @param $offset The offset to set the entry to.
	 * @param $value The value set to the offset.
	 */
	public function offsetSet($offset, $value)
	{
		die(__METHOD__);
	}

	/**
	 * Method unsets and entry within the array.
	 * @param $offset The offset to unset.
	 */
	public function offsetUnset($offset)
	{
		die(__METHOD__);
	}

	/************************************/
	/** End ArrayAccess Implementation **/
	/************************************/

	/************************************/
	/** Start Countable Implementation **/
	/************************************/

	public function count()
	{
		die(__METHOD__);
	}

	/**********************************/
	/** End Countable Implementation **/
	/**********************************/


}
?>
