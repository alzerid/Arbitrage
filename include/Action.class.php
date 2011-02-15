<?
class Action
{
	protected $controller;

	public function __construct($controller)
	{
		$this->controller = $controller;
	}

	private function returnAjax($status, $message, $user=NULL)
	{
		$type = $this->controller->getVariable('returnType');

		switch($type)
		{
			case 'xml':
				$type = RM_XML;
				break;

			case 'user':
				$type = RM_USER;
				break;

			case 'json':
			default:
				$type = RM_JSON;
				break;
		}

		if($type == RM_USER)
			echo $ret;
		else
		{
			$rm = new ReturnMedium($status, $message, $user, $type);
			die($rm->display());
		}

		die();
	}

	protected function ajaxEncapsulate($status, $msg, $user=NULL)
	{
		return array($status, $msg, $user);
	}

	public function doAction()
	{
		$view = $this->controller->getVariable('view');
		$ajax = ($this->controller->getVariable('__ajax__') !== NULL);

		if($ajax)
		{
			$method = $this->controller->getVariable('cmd');;
			if(!method_exists($this, $method))
				$this->controller->throwError("Unable to find ajax method '$method'.");

			list($status, $message, $user) = $this->$method();
			$this->returnAjax($status, $message, $user);
			die();
		}
		else
		{
			$method = "action$view";
			if(!method_exists($this, $method))
				return;
				//$this->controller->throwError("Unable to find action method '$method'.");

			$this->$method();
		}
	}
}
?>
