<?
class ArbitrageException extends Exception
{
	protected $_scope;

	public function __construct($message="", $code=0, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
		$this->_scope = "Arbitrage Framework";
	}

	public function getScope()
	{
		return $this->_scope;
	}

	public function render()
	{
		$type = Application::getConfig()->arbitrage->render;
		switch($type)
		{
			case "ReturnMedium":
				$rm = new ReturnMedium;
				$rm->setErrorNo($ex->getCode());
				$rm->setScope($ex->getScope());
				$rm->setMessage($ex->getMessage());

				echo $rm->render;
				die();
				break;

			default:
			case "View":
				$path = Application::getConfig()->fwrootpath . 'include/GlobalExceptionController.class.php';
				if(Application::getConfig()->errorHandlerClass != NULL)
					$path = Application::getConfig()->approotpath . "app/controllers/" . strtolower(Application::getConfig()->errorHandlerClass) . ".php";

				if(!file_exists($path))
					die("CRITICAL ERROR: UNABLE TO THROW EXCEPTION CORRECTLY");

				require_once($path);
				$controller = new GlobalExceptionController('globalerror', 'error');
				$controller->setException($this);
				$controller->execute();
				break;
		}
	}
}
?>
