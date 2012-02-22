<?
/**
 * ISingleton interface
 */
interface ISingleton
{
	static public function getInstance();
}

/**
 * IFactory interface
 */
interface IFactory
{
	static public function get($type);
}

/**
 * IRenderer interface
 * Describes a renderer object.  A render object is responsible for rendering IRenderable objects.
 */
interface IRenderer
{
	public function __construct(IRenderable $ctx);
	public function getContext();
}

/**
 * IViewRenderer
 */
interface IViewRenderer extends IRenderer
{
	public function render();
}

/**
 * IViewFileRenderer interface
 */
interface IViewFileRenderer extends IRenderer
{
	public function render($file, $layout, $variables);
}

/*
 *
 */
interface IRenderable
{
}

/**
 * IRenderable interface
 */
interface IFileRenderable extends IRenderable
{
	public function renderFile($file, $layout, $variables);
	public function renderPartialFile($file, $variables=NULL);
}

/**
 * IController interface
 */
interface IController
{
	public function renderInternal(IRenderer $renderer);
}

/**
 * IAction interface
 */
interface IAction
{
	public function execute();
}

/**
 * IModel interface
 */
interface IModel
{
	public function save();
	public function update();
	public function getID();

	//Bulk/Single operations
	public function findAll($condition = array());
	public function findOne($condition = array());
	public function remove($condition = array());
}

/**
 * Remote Cache Interface
 */
interface IRemoteCache extends ISingleton
{
	public function connect($address, $port);
	public function close();

	public function get($key, $serialize=true);
	public function set($key, $value, $expire=0, $serialize=true, $flags=NULL);
	public function add($key, $value, $expire=0, $serialize=true, $flags=NULL);
	public function delete($key, $flags=NULL);

	public function increment($key, $value=1, $expire=0);
	public function decrement($key, $value=1, $expire=0);

	/* TODO: Implement these methods
	public function leftPush($key, $value);
	public function leftPop($key, $value);
	public function rightPush($key, $value);
	public function rightPop($key, $value);
	*/
}

/**
 * General Listener
 */
interface IListener
{
}

interface IEvent
{
	public function stopPropagation();
	public function getPropagation();
	public function triggerListeners(array $listeners);
}

/**
 * IErrorHandlerListener implementation
 */
interface IErrorHandlerListener extends IListener
{
	public function handleException(CExceptionEvent $ex);
	public function handleError(CErrorEvent $err);
}

/**
 * Observer implementation
 */
interface IObserver
{
	public function addListener(IListener $listener);
	public function prependListener(IListener $listener);
	public function removeListener(IListener $listener);
	public function clearListeners();
}
?>
