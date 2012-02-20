<?
class ArbitrageAction
{
	private $_controller;
	private $_name;
	private $_exception_listener;

	public function __construct($controller, $action_name)
	{
		$this->_controller         = $controller;
		$this->_name               = $action_name;
		$this->_excetion_listeners = array();
	}

	public function getName()
	{
		return $this->_getName();
	}

	public function execute($vars)
	{
		//TODO: Execute action
	}
}
?>
