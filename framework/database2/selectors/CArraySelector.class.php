<?php
namespace Framework\Database2\Selectors;

class CArraySelector extends \Framework\Database2\Selectors\CSelector
{
	public function __construct(array $selector=array())
	{
		$this->_selector = $selector;
	}

	public function set($selector)
	{
		if(!is_array($selector))
			throw new \EDatabaseDriverException("Selector must be an array.");

		//Set selector
		$this->_selector = $selector;
	}
}
?>
