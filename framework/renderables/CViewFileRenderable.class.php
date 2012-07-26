<?
namespace Arbitrage2\Renderables;
use \Arbitrage2\Exceptions\EArbitrageRenderableException;
$_application->requireRenderable('Arbitrage2.Renderables.CViewFilePartialRenderable');

class CViewFileRenderable extends \Arbitrage2\Renderables\CViewFilePartialRenderable implements \Arbitrage2\Interfaces\ILayoutRenderable
{
	protected $_layout;

	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	public function getLayout()
	{
		return $this->_layout;
	}

	public function render()
	{
		//Setup data
		$default = array('layout'    => $this->_layout,
		                 'variables' => array());
	
		//Merge defaults with data
		isset($this->_content['render']) && $default['render'] = $this->_content['render'];
		isset($this->_content['layout']) && $default['layout'] = $this->_content['layout'];
		$default['variables'] = array_merge($default['variables'], (isset($this->_content['variables'])? $this->_content['variables'] : array()));

		//Call parent render
		$this->_content['render'] = "/{$this->_content['render']}";
		$content = parent::render($default);

		//Now render layout
		$layout = $this->_path . "/layout/{$this->_layout}.php";
		if(!file_exists($layout))
			throw new EArbitrageRenderableException("Layout does not exist '($layout).");

		//Extract the variables
		$_vars = $default['variables'];
		extract($_vars);

		//Require view
		ob_start();
		ob_implicit_flush(false);
		require_once($layout);

		return ob_get_clean();
	}

	public function renderPartial($file, $variables=NULL)
	{
		//Ensure _controller and _application is sent to the view file
		if($variables == NULL)
			$variables = array();

		//Get controller
		if(isset($this->_content['variables']['_controller']))
			$variables['_controller'] = $this->_content['variables']['_controller'];

		//Get application
		if(isset($this->_content['variables']['_application']))
			$variables['_application'] = $this->_content['variables']['_application'];

		//Call parent
		return parent::renderPartial($file, $variables);
	}
}
?>