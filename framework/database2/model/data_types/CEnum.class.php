<?php
namespace Framework\Database2\Model\DataTypes;

class CEnum extends \Framework\Database2\Model\DataTypes\CDataType
{
	protected $_enum;

	public function __construct($val, array $enum)
	{
		$this->_enum = $enum;
		parent::__construct($val);
	}

	/**
	 * Method called when we want to set a value to the DataType.
	 * @param $val The value to set to.
	 */
	public function set($val)
	{
		//Ensure the value exists in the enum
		if(!in_array($val, $this->_enum))
			throw new \Framework\Exceptions\EDatabaseDataTypeException("Value '$val' is not in the enum set.");

		$this->_val = $val;
	}

	/**
	 * Returns a string.
	 */
	protected function _toString()
	{
		return $this->_val;
	}
}
?>
