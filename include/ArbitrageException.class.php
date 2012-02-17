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
		$type = strtolower(Application::getConfig()->arbitrage->renderMode);
		switch($type)
		{
			case "returnmedium":
				$rm = new ReturnMedium;
				$rm->setErrorNo($this->getCode());
				$rm->setScope($this->getScope());
				$rm->setMessage($this->getMessage());

				echo $rm->render();
				die();
				break;

			default:
			case "view":
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

class PHPException extends ArbitrageException
{
	public function __construct($message, $code, $file, $line, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
		$this->file   = $file;
		$this->line   = $line;
		$this->_scope = "PHP Error";
	}
}
?>
