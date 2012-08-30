<?
namespace Framework\Renderables;
use \Framework\Exceptions\EArbitrageRenderableException;
use \Framework\Base\CWebApplication;

class CViewFilePartialRenderable implements \Framework\Interfaces\IViewFileRenderable, \Framework\Interfaces\IViewFileRenderableContext
{
	protected $_path;      //Renderable path to use
	protected $_content;   //The content to render
	protected $_ctx;       //The context to use for rendering

	/**
	 * Method initializes the CViewFilePartialRenderable.
	 * @param string $path The path to the view files.
	 * @param array $content The content to render.
	 */
	public function initialize($path, $content)
	{
		$this->_path    = $path;
		$this->_content = $content;
		$this->setContext($this);
	}

	/**
	 * Method sets the context to which the renderer should use.
	 * @param \Framework\Interfaces\IViewFileRenderableContext $ctx The context to use.
	 */
	public function setContext(\Framework\Interfaces\IViewFileRenderableContext $ctx)
	{
		$this->_ctx = $ctx;
	}

	/**
	 * Method renders the file based off of $_content.
	 */
	public function render()
	{
		//Setup data
		$default = array('variables' => array());

		//Merge defaults with data
		$default['render']    = ((isset($this->_content['render']))? $this->_content['render'] : $default['render']);
		$default['variables'] = array_merge($default['variables'], (isset($this->_content['variables'])? $this->_content['variables'] : array()));

		//Set header
		header("Content-Type: text/html");

		//Get content from view
		return $this->renderPartial($default['render'], $default['variables']);
	}

	/**
	 * Method actually renders the file.
	 * @param string $file The file to render.
	 * @param array $variables The variables to pass to the view file.
	 * @return Returns the content.
	 */
	public function renderPartial($file, $variables=NULL)
	{
		//Get view file
		$path = $this->_path . "/$file.php";
		if(!file_exists($path))
			throw new EArbitrageRenderableException("Unable to load view file ($path).");

		ob_start();
		ob_implicit_flush(false);
		$this->_ctx->renderContext($path, $variables);
		$content = ob_get_clean();

		return $content;
	}

	public function renderContext($file, $_vars=NULL)
	{
		if($_vars !== NULL)
			extract($_vars);

		require($file);
	}
}
?>
