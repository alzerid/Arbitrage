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
	 * Method sets the timezone.
	 * @param $tz The timezone to set to.
	 */
	public function setTimezone(\DateTimeZone $tz)
	{
		$this->_val->setTimeZone($tz);
	}

	/**
	 * Returns a string formatted date.
	 * @param $format The format to use.
	 * @return Returns the formatted string.
	 */
	public function format($format)
	{
		return $this->_val->format($format);
	}

	/**
	 * Returns a string.
	 */
	protected function _toString()
	{
		return $this->format("Y/m/d H:i:s e");
	}
}
?>
