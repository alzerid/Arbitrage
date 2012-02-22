<?
class CAction implements IAction
{
	private $_controller;
	private $_name;

	public function __construct($controller, $action_name)
	{
		$this->_controller = $controller;
		$this->_name       = $action_name . "Action";

		//Ensure controller has action name
		if(!method_exists($controller, $this->_name))
			throw new EArbitrageException("Action '{$this->_name}' does not exist for controller '$controller'.");
	}

	public function getName()
	{
		return strtolower(preg_replace('/Action$/i', '', $this->_name));
	}

	public function execute()
	{
		$ret = $this->_controller->{$this->_name}();

		if(!is_array($ret))
			throw new EArbitrageException("Actions must return an array.");

		//Determine view, layouts, and data
		if(!isset($ret['layout']))
			$ret['layout'] = "default";

		if(!isset($ret['render']))
		{
			$render  = preg_replace('/Controller$/i', '', strtolower($this->_controller->getName()));
			$render .= "/";
			$render .= preg_replace('/Action$/i', '', strtolower($this->_name));
			$ret['render'] = $render;
		}

		if(!isset($ret['variables']))
			$ret['variables'] = array();
		else if(isset($ret['variables']) && !is_array($ret['variables']))
			throw new EArbitrageException("Variables field must be an associative array for '{$this->_controller->getName()} {$this->_name}'.");

		return $ret;
	}
}
?>
