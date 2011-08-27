<?
class Controller extends Component
{
	private $_filters;
	private $_view_vars;
	private $_ajax;

	public function __construct($controller, $action)
	{
		$this->_controller_name = $controller;
		$this->_action_name     = $action;
		$this->_view_vars       = array();
		$this->_ajax            = false;

		parent::__construct();
	}

	//Magic functions
	public function __get($name)
	{
		return $this->_get;
		var_dump($name);
		die();
	}

	public function setAjax($bool)
	{
		$this->_ajax = $bool;
	}

	public function isAjax()
	{
		return $this->_ajax;
	}

	/* Function gets items from form POST or GET */
	public function form($key)
	{
		if(isset($this->_post[$key]))
			return $this->_post[$key];
		elseif(isset($this->_get[$key]))
			return $this->_get[$key];

		return NULL;
	}

	public function execute()
	{
		//Get filters
		$filters = $this->filters();

		//Execute proper action
		$action = $this->_action_name . "action";

		//Run before filter
		$this->_runFilter('before_filter');

		//TODO: Pass params if there are any 
		if(!method_exists($this, $action))
			throw new ArbitrageException("No action by '{$this->_action_name}' is available.");

		//Call the action
		$ret = $this->$action();

		//Run after filter
		$this->_runFilter('after_filter', array('variables' => &$ret['variables']));

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
		$conf = Application::getConfig();

		//Check if view is set
		$view_path = $conf->approotpath . "app/views/$view.php";

		if(isset($_vars) && is_array($_vars))
			extract($_vars);

		ob_start();
		require($view_path);
		$content = ob_get_clean();

		return $content;
	}

	public function render($view, $layout="layout", $_vars=NULL)
	{
		$conf = Application::getConfig();

		//merge _view_vars
		if($_vars != NULL)
			$_vars = array_merge($this->_view_vars, $_vars);
		elseif(count($this->_view_vars))
			$_vars = $this->_view_vars;

		//Check for errors in the system
		$err = Application::getBackTrace();
		if(!empty($err))
			$content = $err;
		else
			$content = $this->renderPartial($view, $_vars);

		if(isset($_vars) && is_array($_vars) && count($_vars))
			extract($_vars);

		//Get layout
		$layout_path = $conf->approotpath . "app/views/layout/$layout.php";
		ob_start();
		require_once($layout_path);
		$content = ob_get_clean();

		//Check if there are post processing filters
		//$stime = microtime();
		$ret = $this->_runFilter('post_process', array('html' => $content));
		//$etime = microtime();

		if(isset($ret))
			$content = $ret;

		//Render layout
		echo $content;
	}

	public function isPost()
	{
		return isset($this->_post['_form']);
	}

	public function isFormPost()
	{
		return (isset($this->_post['_form']) || isset($this->_get['_form']));
	}

	public function includeControllerJavascript($controller=NULL)
	{
		if($controller == NULL)
			$controller = $this->_controller_name;

		//Add inline js for controller creation
		//Application::$_inlinejs .= "";
		Application::includeJavascriptFile("/cjavascript/$controller.js");
	}

	public function includeJavascript($link)
	{
		Application::includeJavascriptFile("/javascript/$link");
	}

	public function includeExternalJavascript($url)
	{
		Application::includeJavascriptFile($url);
	}

	public function includeStylesheet($file)
	{
		Application::includeStylesheetFile("/stylesheets/$file");
	}

	public function includeExternalStylesheet($url)
	{
		Application::includeStylesheetFile($url);
	}

	public function addViewVariables($vars)
	{
		$this->_view_vars = array_merge($this->_view_vars, $vars);
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
		$conf = Application::getConfig();

		//Check if ret is set
		if(!isset($ret) || (is_array($ret) && !isset($ret['render'])))
		{
			if(!isset($ret))
				$ret = array();

			//Get default return type
			$render = ((isset($conf->render['default']))? $conf->render['default'] : 'View');
			switch(strtolower($render))
			{
				case "returnmedium":
					$rm = new ReturnMedium;
					$rm->setMessage('');
					$ret['render'] = $rm;

					break;

				case "view":
					$ret['render'] = $this->_controller_name . "/" . $this->_action_name;
					break;
			}
		}

		//Render based on type
		if($ret['render'] === false)
			return;
		elseif(is_object($ret['render']) && get_class($ret['render']) == "ReturnMedium")
			echo $ret['render']->render();
		elseif(is_string($ret['render']))
		{
			//Get view
			$view = $ret['render'];
			$vars = ((isset($ret['variables']))? $ret['variables'] : NULL);
			$lay  = ((isset($ret['layout']))? $ret['layout'] : "layout");

			//No view specified use index  
			if(!strstr($view, "/"))
				$view = $view . "/index";
			
			//require the file
			$this->render($view, $lay, $vars);
		}
	}

	private function _runFilter($filter, $params=array())
	{
		$filters = $this->filters();
		if(!array_key_exists($filter, $filters))
			return;

		//Get filters
		$filters = $filters[$filter];
		$ret     = "";

		//Run through array
		foreach($filters as $filter)
		{
			if(is_string($filter))
				$ret = $this->$filter($params);
			elseif(is_object($filter) && get_parent_class($filter) == "Filter")
				$ret = $filter->execute($params);
			elseif(is_array($filter))
			{
				$component = $filter['component'];
				$method    = $filter['method'];

				$ret = $component->$method($params);
			}

			if($filter == "post_process")
				return $ret;
		}
	}
}
?>
