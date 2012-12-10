<?php
namespace Framework\Database2\Model\DataTypes;

abstract class CDataType
{
	protected $_val;

	public function __construct($val=NULL)
	{
		$this->set($val);
	}

	public function __toString()
	{
		return $this->_toString();
	}

	/**
	 * Method called when we want to set a value to the DataType.
	 */
	abstract public function set($val);

	/**
	 * Abstract method converts the data type to a string.
	 * @return string Returns a string.
	 */
	abstract protected function _toString();
}
?>
