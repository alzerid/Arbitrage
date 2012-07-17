<?
/**
 * Base Controller
 * @author Eric M. Janik
 * @version 2.0
 */

abstract class CBaseController extends CViewFileRenderable implements IController
{
	//PHP Variables attatched to the session
	protected $_get;
	protected $_post;
	protected $_cookie;
	protected $_request;
	protected $_session;
	protected $_files;
	protected $_flash;
	protected $_content;

	//Controller specifics
	private $_filters;
	private $_renderable;      //The renderer
	private $_ajax;
	private $_action;

	public function __construct()
	{
		parent::__construct();

		//PHP variables
		$this->_get = $_GET;
		unset($this->_get['_route']);

		$this->_post     = $_POST;
		$this->_request  = array_merge($_GET, $_POST);
		$this->_cookie   = $_COOKIE; 
		
		if(isset($_SESSION))
			$this->_session =& $_SESSION;
		else
			$this->_session = NULL;

		$this->_files = ((isset($_FILES))? $_FILES : array());

		//Internal variables
		$this->_ajax     = false;
		$this->_renderable = $this;
		$this->_flash    = NULL;
	}

	/**
	 * Starts a PHP session.
	 */
	public function startSession()
	{
		//Ensure session has NOT started to start the session
		if(!isset($_SESSION))
		{
			session_start();
			$this->_session =& $_SESSION;
		}
	}

	/**
	 * Hijacks a PHP session.
	 */
	public function hijackSession($id)
	{
		session_start();
		$path = session_save_path() . "/sess_$id";

		if(!file_exists($path))
			return false;

		$ret  = session_decode(file_get_contents($path));
		$this->_session =& $_SESSION;

		return true;
	}

	/**
	 * Resets the PHP session.
	 */
	public function resetSession()
	{
		session_unset();
		session_destroy();
	}

	/**
	 * Returns the current controller name.
	 * @return string the controller name.
	 */
	public function getName()
	{
		return preg_replace('/(Ajax)?Controller$/i', '', strtolower(get_class($this)));
	}

	/**
	 * Sets the action for the controller
	 * @params  $action The action to run within the controller context scope.
	 */
	public function setAction(IAction $action)
	{
		$this->_action = $action;
	}

	/**
	 * Get the action for the controller.
	 * @returns the controller object.
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * Set the default renderer type for this controller.
	 * @param $type defines how the controller will render the view.
	 */
	public function setRenderer($type)
	{
		//Check to see if type exists
		if(!class_exists($type))
			throw new EArbitrageException("Invalid renderer type '$type'.");

		$this->_renderable = new $type;

		//Make sure $this->_renderable is of IRenderable
		if(!($this->_renderable instanceof IRenderable))
			throw new EArbitrageException("Renderer class '$type' not of type IRenderable.");
	}

	/**
	 * Get the view type.
	 * @return the view type of the controller.
	 */
	public function getRenderer()
	{
		return $this->_renderable;
	}

	/**
	 * Redirect the browser to a specific location
	 * @param $redirect The url, relative or absolute, to redirect to.
	 */
	public function redirect($redirect)
	{
		//Add flash variable to session
		if($this->_flash !== NULL)
			$this->_session['_flash'] = $this->_flash->toArray();

		$url = new URL($redirect);
		header("Location: " . $url->getURL());
		die();
	}

	/**
	 * Sets the controller as an AJAX controller.
	 * @param ajax The state of the controller.
	*/
	public function setAjax($ajax)
	{
		$this->_ajax = $ajax;
	}

	/**
	 * Determines if the controller is an Ajax controller.
	 * @return boolean true if the controller is ajax else false.
	 */
	public function isAjax()
	{
		return $this->_ajax;
	}

	/*
	 * Returns the request array.
	 */
	 public function getRequest()
	 {
		 return $this->_request;
	 }

	 /*
	  * Method forwards the request to another controller and action.
		*/
	public function forward($forward)
	{
		//Load controller
		return CApplication::getInstance()->forward($forward);
	}

	/**
	 * Method that actually executes the action within the Controller context.
	 * param $render Indicates if the results should be rendered by an IRenderable.
	 */
	public function execute($render=true)
	{
		//Get filters
		$chain = new CFilterChain($this);

		//Run before filter
		$chain->runBeforeFilterChain();

		//Setup flash variables
		$this->_flash = new CFlashPropertyObject();

		//Call the action
		$ret = $this->_action->execute();

		if(!$render)
			return $ret;

		//Set view variables
		$this->_view_variables = array_merge((isset($ret['variables'])? $ret['variables'] : array()), $this->_view_variables);
		$ret['variables']      = $this->_view_variables;

		//Add flash variable to session
		$this->_session['_flash'] = $this->_flash->toArray();

		//Run after filter
		$chain->runAfterFilterChain();

		//TODO: Ensure PHP Exception is on
		ob_start();

		//Render the renderable content
		$content = $this->renderContent($ret);
		$content = $chain->runPostProcess($content);

		echo $content;

		ob_end_flush();
	}

	public function renderContent($content=NULL)
	{
		$out = NULL;
		if(is_array($content))
			$out = $this->_renderable->render($content);
		elseif($content instanceof IRenderable)
			$out = $content->render();

		if($out === NULL)
			throw new EArbitrageException("Content is NULL. Check your renderable type.");

		return $out;
	}

	/**
	 * Sets a specific view variable to a value.
	 */
	public function setViewVariable($var, $val)
	{
		$this->_view_variables[$var] = $val;
	}

	/**
	 * Returns the view variable based on the key.
	 * @return mixed Returns a mixed result based on the value.
	 */
	public function getViewVariable($key)
	{
		return ((isset($this->_view_variables[$key]))? $this->_view_variables[$key] : NULL);
	}

	/**
	 * Returns the entire view variable array.
	 * @return array view variables.
	 */
	public function getViewVariables()
	{
		return $this->_view_variables;
	}

	/**
	 * Adds a view variable to the array.
	 * @return mixed Adds a variable to the view variable array.
	 */
	public function addViewVariables($vars)
	{
		$this->_view_variables = array_merge($this->_view_variables, $vars);
	}

	public function filters()
	{
		return array();
	}
}
?>
