<?php
namespace Framework\Interfaces;

/*********************************/
/** Start of Interface Patterns **/
/*********************************/

/**
 * ISingleton interface
 */
interface ISingleton
{
	static public function getInstance();
}

/**
 * IInstantiate interface
 */
interface IInstantiate
{
	static public function instantiate($var);
}

/**
 * IFactory interface
 */
interface IFactory
{
	static public function get($type);
}
/*******************************/
/** End of Interface Patterns **/
/*******************************/

/*********************/
/** Form Interfaces **/
/*********************/

interface IFormElement
{
	public function __toString();
	public function setValue($value);
	public function getValue();
}

interface ISubForm extends IFormElement
{
	public function __construct($name, $form, $options=array());
}

interface ICustomFormElement extends IFormElement
{
	public function __construct($id, $value, $args=NULL);
}

/*************************/
/** End Form Interfaces **/
/*************************/

/**
 * Value Object Interface
 */
interface IValueObject
{
	public function getValue();
	public function setValue();
}

/**
 * To String Interface
 */
interface IStringable
{
	public function _toString();
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
 * IDriver
 */
interface IDriver
{
	public function __construct($config);
	public function getHandle();
	public function getConfig();
}

/**
 * IDatabaseDriver
 */
interface IDatabaseDriver extends IDriver
{
	public function setDatabase($database);
	public function setTable($table);
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
 * Context for rendering.
 */
interface IViewFileRenderableContext
{
	public function renderContext($file, $_vars=NULL);
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
	public function setContext(\Framework\Interfaces\IViewFileRenderableContext $ctx);
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
 * Model Structure Type
 */
interface IModel
{
	public function toArray();
	//public function clear();
}

/**
 * Model Data Type Interface
 */
interface IModelDataType extends IValueObject
{
}

/**
 * Database Model
 */
interface IDatabaseModel extends IModel
{
	public function __construct($data=NULL, \Framework\Interfaces\IDatabaseDriver $driver=NULL);
	public function save();
	public function update();
	public function insert();
	public function remove();
	public function getID();
}

/**
 * IDatabaseModelStructure
 */
interface IDatabaseModelStructure
{
	public function getUpdateQuery($pkey=NULL);
	public function getQuery();
	public function toArray();
	public function setDriver(\Framework\Interfaces\IDatabaseDriver $driver);
	public function clear();
	public function merge();
}

/**
 * Remote Cache Interface
 */
interface IRemoteCache
{
	public function __construct($config);

	public function connect();
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

interface IHTMLDataTableType
{
	public function render(IHTMLDataTable $table, $entry);
}

/** END HTML Interfaces **/

/** Service Interfaces **/
interface IErrorHandlerService
{
	public function handleEvent(IEvent $event);
}
/** End Service Interfaces **/

/**
 * IAPath similiar to XPATH
 */
interface IAPath
{
	public function apath($path);
}

/**
 * IXPath implementation
 */
interface IXPath
{
	public function xpath($path);
}


/****************************/
/** Start Arguments Parser **/
/****************************/

interface IArgument { }

interface ICommandArgument extends IArgument
{
	public function execute();
	public function setApplication(\Framework\Base\CCLIApplication $application);
	public function getCommand();
	public function getDescription();
	public function parse(array $args);
	public function help();
}

interface ICommandParentArgument extends ICommandArgument
{
	public function addChildCommand(\Framework\CLI\ICommandArgument $command);
	public function getChildCommand($command);
	public function childCommandExists($command);
	public function childHelp();
}

interface IOptionArgument extends IArgument
{
	public function getLongOpt();
	public function getShortOpt();
	public function getValue();
	public function setValue($arg);
}

/**************************/
/** End Arguments Parser **/
/**************************/
?>
