<?
namespace Framework\ErrorHandler;

class CErrorHandlerService extends \Framework\Base\CService implements \Framework\Interfaces\IErrorHandlerService
{
	static protected $_SERVICE_TYPE = "errorHandler";

	public function initialize()
	{
		$this->requireServiceFile("CErrorHandlerObserver");
		$this->requireServiceFile("CErrorHandlerController");
	}

	/**
	 * Method handles the event in an appropriate manner.
	 * @param \Framework\Interfaces\IEvent $event The event to handle
	 */
	public function handleEvent(\Framework\Interfaces\IEvent $event)
	{
		//Forward event
		$application = $this->getApplication();
		$application->forward("Framework.ErrorHandler.CErrorHandlerController.handle", array('event' => serialize($event)));

		//Stop event
		$event->stopPropagation();
		$event->preventDefault();
		die();
	}
}
?>
