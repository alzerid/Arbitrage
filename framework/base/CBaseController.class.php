<?
/**
 * Base Controller
 * @author Eric M. Janik
 * @version 2.0
 */

//TODO: Possibly move _layout_path, _view_path, _default_layout to CController
//TODO: Possibly move the rendering IFileRenerable implementation to the CController class
abstract class CBaseController implements IController
{
	//PHP Variables attatched to the session
	protected $_get;
	protected $_post;
	protected $_cookie;
	protected $_request;
	protected $_session;
	protected $_files;
	protected $_flash;
	protected $_view_variables;

	//Controller specifics
	private $_filters;
	private $_renderer_type;
	private $_ajax;
	private $_action;

	public function __construct()
	{
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
		$this->_ajax           = false;
		$this->_renderer_type  = "view";
		$this->_flash          = NULL;
		$this->_view_variables = array();
	}

	/**
	 * Starts a PHP session.
	 */
	public function startSession()
	{
		session_start();
		$this->_session =& $_SESSION;
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
	public function setRendererType($type)
	{
		$this->_renderer_type = $type;
	}

	/**
	 * Get the view type.
	 * @return the view type of the controller.
	 */
	public function getRendererType()
	{
		return $this->_renderer_type;
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
	 * Determines if the controller is an Ajax controller.
	 * @return boolean true if the controller is ajax else false.
	 */
	public function isAjax()
	{
		return preg_match('/Ajax$/', $this->getName());
		//TODO: Get class name, if it has ajax in it then we can determine that it is indeed an ajax controller
		var_dump($this);
		die();
		return $this->_ajax;
	}

	/*
	 * Returns the request array.
	 */
	 public function getRequest()
	 {
		 return $this->_request;
	 }

	/**
	 * Method that actually executes the action within the Controller context.
	 */
	public function execute()
	{
		//Get filters
		$chain = new CFilterChain($this);

		//Run before filter
		$chain->runBeforeFilterChain();

		//Setup flash variables
		$this->_flash = new CFlashPropertyObject();

		//Call the action
		$ret = $this->_action->execute();

		//Set view variables
		if(isset($ret['variables']))
			$this->_view_variables = array_merge($ret['variables'], $this->_view_variables);

		//Add flash variable to session
		$this->_session['_flash'] = $this->_flash->toArray();

		//Run after filter
		$chain->runAfterFilterChain();

		//TODO: Ensure PHP Exception is on
		ob_start();
		$content = $this->render($ret);
		$content = $chain->runPostProcess($content);

		echo $content;

		//Flush output buffer
		ob_end_flush();
	}

	public function render($ret)
	{
		$content = NULL;
		if(isset($ret['render']) && is_string($ret['render']))
		{
			//Determine view type
			if($this->_renderer_type === "view")
			{
				$renderer = new CViewFileRenderer($this);
				$content  = $renderer->render($ret['render'], $ret['layout'], $ret['variables']);
			}
			/*else if($this->_renderer_type === "text")
			{
				$renderer = new CViewTextRenderer($this);
				$content  = $renderer->render($ret['render'], $ret['layout'], $ret['variables']);
			}*/
		}
		elseif(is_array($ret))
		{
			if($this->_renderer_type === "json")
			{
				$renderer = new CJSONRenderer($this);
				$content  = $renderer->render($ret);
			}
		}
		elseif($ret['render'] instanceof IViewRenderer)
			$content = $ret['render']->render();
		else
			throw new EArbitrageException("Unknown renderer returned.");

		if($content === NULL)
			throw new EArbitrageException("Content is NULL. Check your rendering type.");

		return $content;
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

	protected function filters()
	{
		return array();
	}
}
?>
