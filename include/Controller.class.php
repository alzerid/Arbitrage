<?
class Controller extends Component
{
	private $_filters;
	private $_js_controllers;

	public function __construct($controller, $action)
	{
		$this->_controller_name = $controller;
		$this->_action_name     = $action;

		parent::__construct();
	}

	public function execute()
	{
		//Get filters
		$filters = $this->filters();

		//Execute proper action
		$action = $this->_action_name . "action";

		//Run before filter
		$before_filter = ((isset($filters['before_filter']))? $filters['before_filter'] : NULL);
		$this->_runFilter($before_filter);

		//TODO: Pass params if there are any 
		$ret = $this->$action();

		//Handle return
		$this->_handleReturn($ret);
	}

	public function renderPartial($view, $_vars=NULL)
	{
		global $_conf;

		//Check if view is set
		$view_path = $_conf['approotpath'] . "views/$view.php";

		if(isset($_vars) && is_array($_vars))
			extract($_vars);

		ob_start();
		require_once($view_path);
		$content = ob_get_contents();
		ob_clean();

		return $content;
	}

	public function render($view, $vars=NULL)
	{
		global $_conf;
		
		$content = $this->renderPartial($view, $vars);

		if(isset($vars) && is_array($vars))
			extract($vars);

		//Get layout and render
		$layout_path = $_conf['approotpath'] . "views/layout/layout.php";
		require_once($layout_path);
	}

	public function isPost()
	{
		return isset($this->_post['_form']);
	}

	public function includeControllerJavascript($controller=NULL)
	{
		if($controller == NULL)
			$controller = $this->_controller_name;

		$this->_js_controllers[] = "/cjavascript/$controller.js";
	}

	protected function _getComponent($component)
	{
		global $_components;


		if(isset($_components[$component]))
		{
			$component = $_components[$component];
			$component->_controller_name = $this->_controller_name;
			$component->_action_name     = $this->_action_name;

			return $component;
		}

		return NULL;
	}

	protected function filters()
	{
		return array();
	}

	protected function _cssLink($link)
	{
		return "<link rel='stylesheet' type='text/css' href='/stylesheets/$link' />\n";
	}

	protected function _javascriptLink($link)
	{
		return "<script language='JavaScript' src='/javascript/$link'></script>\n";
	}

	protected function _populateJSControllers()
	{
		$ret = '';
		foreach($this->_js_controllers as $js)
			$ret .= "<script language='JavaScript' src='$js'></script>\n";

		return $ret;
	}


	private function _handleReturn($ret)
	{
		global $_conf;

		//Get view
		$view = ((isset($ret['render']))? $ret['render'] : $this->_controller_name . "/" . $this->_action_name);
		$vars = ((isset($ret['variables']))? $ret['variables'] : NULL);

		//No view specified use index  
		if(!strstr($view, "/"))
			$view = $view . "/index";
		
		//require the file
		$this->render($view, $vars);
	}

	private function _runFilter($filters)
	{
		if($filters == NULL)
			return;

		//Run through array
		foreach($filters as $filter)
		{
			if(is_string($filter))
				$this->$filter();
			elseif(is_object($filter) && get_parent_class($filter) == "Filter")
				$filter->execute();
			elseif(is_array($filter))
			{
				$component = $filter['component'];
				$method    = $filter['method'];

				$component->$method();
			}
		}
	}
}
?>
