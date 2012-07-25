<?
namespace Arbitrage2\Base;

abstract class CApplication extends CPackage implements \Arbitrage2\Interfaces\IErrorHandlerListener
{
	/**
	 * Initializes the arbitrage application, loads the application config.
	 * @param string $path The path where the application resides in.
	 * @param string $namespace The namespace associated with the object.
	 */
	public function initialize($path, $namespace)
	{
		//Setup error handler
		\Arbitrage2\ErrorHandler\CErrorHandlerObserver::getInstance()->addListener($this);

		//Call CPackage::initialize
		parent::initialize($path, $namespace);
	}

	/**
	 * Abstract method that all applications must contain. This is called
	 * upon application execution.
	 */
	abstract public function run();

	/**
	 * Method handles errors.
	 */
	public function handleError(\Arbitrage2\Interfaces\IEvent $event)
	{
		die('CApplication::handleError');
	}
	
	/**
	 * Methods handles exceptions.
	 */
	public function handleException(\Arbitrage2\Interfaces\IEvent $event)
	{
		die('CApplication::handleException');
	}
}
?>
