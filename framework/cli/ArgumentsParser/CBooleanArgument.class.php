<?php
namespace Framework\CLI\ArgumentParser;

class CBooleanArgument extends CBaseArgument
{
	public function __construct($short, $long, $default=false, $description="No Description")
	{
		parent::__construct($short, $long, $description);
		$this->_value = $default;
	}
}
?>
