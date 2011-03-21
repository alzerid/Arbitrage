<?
class Controller extends Component
{
	private $_filters;

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

	public function renderModule($module, $opts=array())
	{
		$module = $this->getModule($module, $opts);
		return $module->render();
	}

	public function renderPartial($view, $_vars=NULL)
	{
		global $_conf;

		//Check if view is set
		$view_path = $_conf['approotpath'] . "app/views/$view.php";

		if(isset($_vars) && is_array($_vars))
			extract($_vars);

		ob_start();
		require_once($view_path);
		$content = ob_get_clean();

		return $content;
	}

	public function render($view, $vars=NULL)
	{
		global $_conf;
		
		$content = $this->renderPartial($view, $vars);

		if(isset($vars) && is_array($vars))
			extract($vars);

		//Get layout and render
		$layout_path = $_conf['approotpath'] . "app/views/layout/layout.php";
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

		Application::includeJavascriptFile("/cjavascript/$controller.js");
	}

	public function includeJavascript($link)
	{
		Application::includeJavascriptFile("/javascript/$link");
	}

	public function includeStylesheet($file)
	{
		Application::includeStylesheetFile("/stylesheets/$file");
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

	private function _handleReturn($ret)
	{
		global $_conf;

		if(isset($ret['render']) && get_class($ret['render']) == "ReturnMedium")
			echo $ret['render']->render();
		else
		{
			//Get view
			$view = ((isset($ret['render']))? $ret['render'] : $this->_controller_name . "/" . $this->_action_name);
			$vars = ((isset($ret['variables']))? $ret['variables'] : NULL);

			//if view is fales then return
			if($view === false)
				return;

			//No view specified use index  
			if(!strstr($view, "/"))
				$view = $view . "/index";
			
			//require the file
			$this->render($view, $vars);
		}
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
