<?
namespace Framework\Renderables;
use \Framework\Exceptions\EArbitrageRenderableException;
$_application->requireRenderable('Framework.Renderables.CViewFilePartialRenderable');

class CViewFileRenderable extends \Framework\Renderables\CViewFilePartialRenderable implements \Framework\Interfaces\ILayoutRenderable
{
	protected $_layout;

	public function initialize($path, $content)
	{
		parent::initialize($path, $content);
		$this->_layout = 'default';
	}

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
		$layout = $this->_findPath("/layout/{$this->_layout}.php");
		if($layout===NULL)
			throw new \Framework\Exceptions\EArbitrageRenderableException("Layout does not exist '($layout).");

		//Set header
		header("Content-Type: text/html");

		//Extract the variables
		return $this->renderPartial("/layout/{$this->_layout}", array_merge(array('content' => $content), $default['variables']));
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
