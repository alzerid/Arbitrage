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
	protected $_session;
	protected $_files;

	//Controller specifics
	private $_filters;
	private $_view_vars;
	private $_ajax;
	private $_action;

	public function __construct()
	{
		//PHP variables
		$this->_get = $_GET;
		unset($this->_get['_route']);

		$this->_post     = $_POST;
		$this->_cookie   = $_COOKIE; 
		
		if(isset($_SESSION))
			$this->_session =& $_SESSION;
		else
			$this->_session = NULL;

		$this->_files = ((isset($_FILES))? $_FILES : array());

		//Internal variables
		$this->_view_vars       = array();
		$this->_ajax            = false;
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
	 * Returns the current controller name.
	 * @return string the controller name.
	 */
	public function getName()
	{
		return get_class($this);
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
	 * Redirect the browser to a specific location
	 * @param $redirect The url, relative or absolute, to redirect to.
	 */
	public function redirect($redirect)
	{
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

	/**
	 * Method that actually executes the action within the Controller context.
	 */
	public function execute()
	{
		//Get filters
		$chain = new CFilterChain($this);

		//Run before filter
		$chain->runBeforeFilterChain();

		//Call the action
		$ret = $this->_action->execute();

		//TODO: Set global view variables

		//Run after filter
		$chain->runAfterFilterChain();

		//TODO: Ensure PHP Exception is on
		ob_start();
		$content = $this->_render($ret);
		$content = $chain->runPostProcess($content);

		echo $content;

		//Flush output buffer
		ob_end_flush();
	}

	private function _render($ret)
	{
		$renderer = NULL;
		if(is_string($ret['render']))
		{
			$renderer = new CViewFileRenderer($this);
			$content  = $renderer->render($ret['render'], $ret['layout'], $ret['variables']);
		}
		elseif($ret['render'] instanceof IViewRenderer)
			$content = $ret['render']->render();
		else
			throw new EArbitrageException("Unknown renderer returned.");

		return $content;
	}




	public function getViewVariable($key)
	{
		return ((isset($this->_view_vars[$key]))? $this->_view_vars[$key] : NULL);
	}

	public function getViewVariables()
	{
		return $this->_view_vars;
	}

	public function addViewVariables($vars)
	{
		$this->_view_vars = array_merge($this->_view_vars, $vars);
	}

	protected function filters()
	{
		return array();
	}
}
?>
