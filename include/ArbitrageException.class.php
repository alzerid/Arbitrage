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
		//Use errorHandlerRenderMode if set
		$type = Application::getConfig()->arbitrage->errorHandlerRenderMode;
		$type = (($type==NULL)? Application::getConfig()->arbitrage->renderMode : $type);
		$type = strtolower($type);
		switch($type)
		{
			case "returnmedium":
				$rm = $this->_renderReturnMedium();
				echo $rm->render();
				die();
				break;

			default:
			case "view":
				$this->_renderView();
				break;
		}
	}

	protected function _renderReturnMedium()
	{
		$rm = new ReturnMedium;
		$rm->setErrorNo($this->getCode());
		$rm->setScope($this->getScope());
		$rm->setMessage($this->getMessage());

		return $rm;
	}

	protected function _renderView()
	{
		$path = Application::getConfig()->fwrootpath . 'include/ExceptionHandler.class.php';
		if(Application::getConfig()->errorHandlerClass != NULL)
			$path = Application::getConfig()->approotpath . "app/controllers/" . strtolower(Application::getConfig()->errorHandlerClass) . ".php";

		if(!file_exists($path))
			die("CRITICAL ERROR: UNABLE TO THROW EXCEPTION CORRECTLY");

		require_once($path);
		$controller = new ExceptionHandler('globalerror', 'error');
		$controller->setException($this);
		$controller->execute();
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

	protected function _renderReturnMedium()
	{
		$rm = parent::_renderReturnMedium();
		$rm->setUser(array('file' => $this->getFile(), 'line' => $this->getLine()));

		return $rm;
	}
}
?>
