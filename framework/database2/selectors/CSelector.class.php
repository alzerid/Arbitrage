<?php
namespace Framework\Database2\Selectors;

abstract class CSelector
{
	protected $_selector;

	/**
	 * Method returns the raw selector.
	 */
	public function getRawSelector()
	{
		return $this->_selector;
	}

	/**
	 * Method sets the selector data.
	 * @param $selector The data to set to.
	 */
	abstract public function set($selector);
}
?>
