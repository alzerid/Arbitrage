<?php
namespace Framework\CLI\ArgumentParser;

class CValueArgument extends \Framework\CLI\ArgumentParser\CBaseArgument
{
	public function __construct($short, $long, $default=NULL, $description="No Descriptoin")
	{
		parent::__construct($short, $long);
		$this->_value = $default;
	}
}
?>
