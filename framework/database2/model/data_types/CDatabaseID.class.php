<?php
namespace Framework\Database2\Model\DataTypes;

class CDatabaseID extends \Framework\Database2\Model\DataTypes\CDataType
{
	/**
	 * Method called to set the the value of this DataType.
	 * @param $val The value to set to.
	 */
	public function set($val)
	{
		$this->_val = $val;
	}

	/**
	 * Abstract method converts the data type to a string.
	 * @return string Returns a string.
	 */
	protected function _toString()
	{
		return (string) $this->_val;
	}
}
?>
