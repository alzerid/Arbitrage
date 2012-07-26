<?
namespace Framework\ErrorHandler;

class CErrorHandlerController extends \Framework\Base\CController
{
	private $_err_vars;
	private $_err_file;

	public function handleAction()
	{
		@ob_end_clean();
		//Unserialize event
		$event = unserialize($this->_request['event']);

		//Check if debug is on
		$config = $this->_application->getConfig();
		if($config->arbitrage2->debugMode)
		{
			//Use internal view
			$fwpath  = \Framework\Base\CKernel::getInstance()->getPath() . "/framework/views/";

			//Setup content
			$content = array();
			$content['render']    = 'errors/exception';
			$content['variables'] = array('event' => $event);

			//Setup renderable
			$this->requireRenderable('Arbitrage2.Renderables.CViewFilePartialRenderable');
			$renderable = new \Framework\Renderables\CViewFilePartialRenderable;
			$renderable->initialize($fwpath, $content);
			return $renderable;
		}

		die('CErrorHandlerController::handleAction');
	}

	public function processAction()
	{
		if($this->_err_vars === NULL)
			$this->_err_vars = array();

		//Ensure CViewFileRenderable is our renderable
		$this->setRenderer('CViewFileRenderable');

		//Check for application files
		$file = $this->_err_file;
		$path = CApplication::getConfig()->_internals->approotpath . "app/views/_internal/errors/$file.php";
		if(!file_exists($path))  //set viewpath to framework
			$this->getRenderer()->setViewPath(CApplication::getConfig()->_internals->fwrootpath . "framework/views/");
		else
		{
			$this->getRenderer()->setViewPath(CApplication::getConfig()->_internals->approotpath . "app/views/_internal/");
			$this->getRenderer()->setLayoutPath(CApplication::getConfig()->_internals->approotpath . "app/views/_internal/errors/");
			$this->getRenderer()->setLayout('layout');
		}

		return array('render' => "errors/$file", 'variables' => $this->_err_vars);
	}
}
?>
