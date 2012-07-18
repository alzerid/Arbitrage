<?
class CViewFileRenderable extends CViewFilePartialRenderable
{
	protected $_view_variables;
	protected $_layout_paths;
	protected $_layout;

	public function __construct()
	{
		parent::__construct();

		$this->_layout_paths   = array();
		$this->_view_variables = array();

		$this->addLayoutPath(CApplication::getConfig()->_internals->approotpath . "app/views/layout/");
		$this->setLayout("default");
	}

	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	public function getLayout()
	{
		return $this->_layout;
	}

	public function getLayoutPath()
	{
		return $this->_layout_paths;
	}

	public function addLayoutPath($path)
	{
		$this->_layout_paths[] = $path;
	}

	public function render($data=NULL)
	{
		//Setup data
		$default = array('render'    => CApplication::getInstance()->getController()->getName() . "/" . CApplication::getInstance()->getController()->getAction()->getName(),
		                 'layout'    => $this->_layout,
		                 'variables' => array());

		//Merge defaults with data
		isset($data['render']) && $default['render'] = $data['render'];
		isset($data['layout']) && $default['layout'] = $data['layout'];
		$default['variables'] = array_merge($default['variables'], (isset($data['variables'])? $data['variables'] : array()));

		//Call parent render
		$content = parent::render($default);

		//Now render layout
		$layout = NULL;
		foreach($this->_layout_paths as $lp)
		{
			if(file_exists($lp . "/" . $default['layout'] . ".php"))
			{
				$layout = $lp . "/" . $default['layout'] . ".php";
				break;
			}
		}

		if(!file_exists($layout))
			throw new EArbitrageException("Layout does not exist '{$default['layout']}'.");

		//Extract the variables
		$_vars = $default['variables'];
		$_controller = CApplication::getInstance()->getController();
		$_action     = CApplication::getInstance()->getController()->getAction();
		extract($_vars);

		//Require view
		ob_start();
		ob_implicit_flush(false);
		require_once($layout);

		return ob_get_clean();
	}
}
?>
