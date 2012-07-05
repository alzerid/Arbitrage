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
	public function renderContent($content);
}

/*
 *
 */
interface IRenderable
{
	public function render();
}

/**
 * IViewFileRenderable interface
 */
interface IViewFileRenderable extends IRenderable
{
	public function renderPartial($file, $vars);
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
 * ITemplate interface
 */
interface ITemplate
{
	public function render($variables);
}

/**
 * IController interface
 */
interface IController extends IRenderer  { }

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

	public function leftPush($key, $value);
	public function leftPop($key);
	public function rightPush($key, $value);
	public function rightPop($key);
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
 * IEventListener implementation
 */
interface IEventListener
{
	public function handleEvent(CEvent $event);
}

/**
 * IErrorHandlerListener implementation
 */
interface IErrorHandlerListener extends IListener
{
	public function handleException(CExceptionEvent $event);
	public function handleError(CErrorEvent $event);
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

/** HTML Interfaces **/

/**
 * DataTable interface
 */
interface IHTMLDataTable
{
	public function __construct($id, $headers, $data, $attrs=array());
	public function render();
}

interface IHTMLDataTableEntry
{
	public function render(IHTMLDataTable $table, array $entry);
}

/** END HTML Interfaces **/

?>
