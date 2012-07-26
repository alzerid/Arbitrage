<?
namespace Framework\Renderables;
use \Arbitrage2\Exceptions\EArbitrageRenderableException;
use \Arbitrage2\Base\CWebApplication;

class CViewFilePartialRenderable implements \Arbitrage2\Interfaces\IViewFileRenderable
{
	protected $_path;
	protected $_content;

	/**
	 * Method initializes the CViewFilePartialRenderable.
	 * @param string $path The path to the view files.
	 * @param array $content The content to render.
	 */
	public function initialize($path, $content)
	{
		$this->_path    = $path;
		$this->_content = $content;
	}

	public function render()
	{
		//Setup data
		$default = array('variables' => array());

		//Merge defaults with data
		$default['render']    = ((isset($this->_content['render']))? $this->_content['render'] : $default['render']);
		$default['variables'] = array_merge($default['variables'], (isset($this->_content['variables'])? $this->_content['variables'] : array()));

		//Get content from view
		return $this->renderPartial($default['render'], $default['variables']);
	}

	public function renderPartial($file, $variables=NULL)
	{
		$_vars = $variables;
		if($_vars !== NULL)
			extract($_vars);
		
		//Get view file
		$path = $this->_path . "/$file.php";
		if(!file_exists($path))
			throw new EArbitrageRenderableException("Unable to load view file ($path).");

		ob_start();
		ob_implicit_flush(false);
		require($path);
		$content = ob_get_clean();

		return $content;
	}
}

?>
