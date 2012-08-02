<?
namespace Framework\Interfaces;

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
 * IModuleLoader interface
 * Interface used when loading modules (see CDatabaseDriverFactory)
 */
interface IModuleLoader extends ISingleton
{
	public function registerPath($path);
	public function load($driver, $config);
	public function getHandle($driver, $config);
}

/**
 * IAutoLoadListener
 */
interface IAutoLoadListener
{
	public function handleAutoLoad(\Framework\Interfaces\IEvent $event);
}

/**
 * IAutoLoadObserver
 */
interface IAutoLoadObserver
{
	public function registerAutoLoadListener(\Framework\Interfaces\IAutoLoadListener $handler);
	public function handleAutoLoad($class);
}

/**
 * IRenderer interface
 * Describes a renderer object.  A render object is responsible for rendering IRenderable objects.
 */
interface IRenderer
{
	public function render($content);
}

/*
 *
 */
interface IRenderable
{
	public function render();
}

/**
 * Content Renderable Interface
 */
interface IContentRenderable extends IRenderable
{
	public function initialize($content);
}

/**
 * IViewFileRenderable interface
 */
interface IViewFileRenderable extends IRenderable
{
	public function initialize($application, $content);
	public function renderPartial($file, $vars);
}

/**
 * ILayoutRenderable interface
 */
interface ILayoutRenderable
{
	public function setLayout($layout);
	public function getLayout();
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
	public function handleException(IEvent $event);
	public function handleError(IEvent $event);
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

/** Service Interfaces **/
interface IErrorHandlerService
{
	public function handleEvent(IEvent $event);
}
/** End Service Interfaces **/

?>
