<?php
namespace Framework\Database2\Model\DataTypes;

class CDate extends \Framework\Database2\Model\DataTypes\CDataType
{
	/**
	 * Method called when we want to set a value to the DataType.
	 * @param $val The value to set to.
	 * @param $tz The timezone to use.
	 */
	public function set($val, $tz=NULL)
	{
		if(is_string($val))
			$this->_val = new \DateTime($val, $tz);
		elseif(gettype($val) == "integer")
		{
			$this->_val = new \DateTime("now", $tz);
			$this->_val->setTimestamp($val);
		}
		elseif($val instanceof \DateTime)
			$this->_val = clone $val;
		elseif($val instanceof \Framework\Database2\Model\DataTypes\CDate)
			$this->_val = clone $val->getValue();
		elseif($val === NULL)
			$this->_val = new \DateTime();
		else
			throw new \Framework\Exceptions\EDatabaseDataTypeException("Unable to handle data value '$val'.");
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
	 * Method returns the unix timestamp.
	 * @return int Returns the unix timestamp.
	 */
	public function getTimestamp()
	{
		return $this->_val->getTimestamp();
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
