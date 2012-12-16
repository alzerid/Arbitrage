<?php
namespace Framework\CLI\ArgumentParser;

abstract class CBaseArgument implements \Framework\Interfaces\IOptionArgument
{
	protected $_value;
	private $_short;
	private $_long;
	private $_description;

	public function __construct($short, $long, $description="NO DESCRIPTION")
	{
		$this->_description = $description;
		$this->_short       = $short;
		$this->_long        = $long;
		$this->_value       = NULL;
	}

	/**
	 * Method returns the long argument.
	 * @return string Returns the long argument.
	 */
	public function getLongOpt()
	{
		return $this->_long;
	}

	/**
	 * Method retuns the short argument.
	 * @return string Returns the short argument.
	 */
	public function getShortOpt()
	{
		return $this->_short;
	}

	/**
	 * Method returns the value of the argument.
	 * @return mixed Returns the value of the argument.
	 */
	public function getValue()
	{
		return $this->_value;
	}

	/** 
	 * Method sets the value of the argument.
	 * @param $val The value to set to.
	 */
	public function setValue($val)
	{
		$this->_value = $val;
	}

	/**
	 * Method returns the description of the argument.
	 * @return srtring Returns the description of the argument.
	 */
	public function getDescription()
	{
		return $this->_description;
	}
}
?>
