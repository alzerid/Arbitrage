<?
class Controller
{
	protected $_get;
	protected $_post;
	protected $_cookie;
	protected $_session;

	private $_controller;
	private $_action;

	public function __construct($controller, $action)
	{
		$this->_controller = $controller;
		$this->_action     = $action;
	}

	public function execute()
	{
		//Setup params
		$this->_setupParams();

		//Execute proper action
		$action = $this->_action . "action";

		//TODO: Pass params if there are any 
		$ret = $this->$action();

		//Handle return
		$this->_handleReturn($ret);
	}

	public function renderPartial($file)
	{
	}

	public function render($view, $vars=NULL)
	{
		global $_conf;

		//Check if view is set
		$view_path = $_conf['approotpath'] . "views/$view.php";

		if(isset($vars) && is_array($vars))
			extract($vars);

		ob_start();
		require_once($view_path);
		$content = ob_get_contents();
		ob_clean();

		//Get layout and render
		$layout_path = $_conf['approotpath'] . "views/layout/layout.php";
		require_once($layout_path);
	}

	private function _handleReturn($ret)
	{
		global $_conf;

		//Get view
		$view = ((isset($ret['render']))? $ret['render'] : $this->_controller . "/" . $this->_action);
		$vars = ((isset($ret['variables']))? $ret['variables'] : NULL);

		//No view specified use index  
		if(!strstr($view, "/"))
			$view = $view . "/index";
		
		//require the file
		$this->render($view, $vars);
	}

	private function _setupParams()
	{
		$this->_get = $_GET;
		unset($this->_get['_route']);

		$this->_post    = $_POST;
		$this->_cookie  = $_COOKIE; 
		$this->_session = (isset($_SESSION)? $_SESSION : NULL);
	}
}
?>
