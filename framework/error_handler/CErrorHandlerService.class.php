<?
namespace Arbitrage2\ErrorHandler;

class CErrorHandlerService extends \Arbitrage2\Base\CService implements \Arbitrage2\Interfaces\IErrorHandlerService
{
	static protected $_SERVICE_TYPE = "errorHandler";

	public function initialize()
	{
		$this->requireServiceFile("CErrorHandlerObserver");
		$this->requireServiceFile("CErrorHandlerController");
	}

	/**
	 * Method handles the event in an appropriate manner.
	 * @param \Arbitrage2\Interfaces\IEvent $event The event to handle
	 */
	public function handleEvent(\Arbitrage2\Interfaces\IEvent $event)
	{
		//Forward event
		$application = $this->getApplication();
		$application->forward("Arbitrage2.ErrorHandler.CErrorHandlerController.handle", array('event' => serialize($event)));

		//Stop event
		$event->stopPropagation();
		$event->preventDefault();
		die();
	}
}
?>
