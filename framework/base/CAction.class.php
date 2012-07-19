<?
namespace Arbitrage2\Base;
use \Arbitrage2\Interfaces\IAction;

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
		{
			if(CWebApplication::getConfig()->server->debugMode)
				throw new EArbitrageException("Action '{$this->_name}' does not exist for controller '{$this->_controller->getName()}'.");
			else
				throw new EHTTPException(EHTTPException::$HTTP_BAD_REQUEST);
		}
	}

	public function getName()
	{
		return strtolower(preg_replace('/Action$/i', '', $this->_name));
	}

	public function execute()
	{
		$ret = $this->_controller->{$this->_name}();

		//Default action
		if($ret === NULL)
			$ret = array();

		if(!is_array($ret) && !($ret instanceof IRenderable))
			throw new EArbitrageException("Actions must return an array or of type IRenderable.");

		return $ret;
	}
}
?>
