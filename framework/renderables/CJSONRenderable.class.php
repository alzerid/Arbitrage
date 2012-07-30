<?
namespace Framework\Renderables;
use \Framework\Interfaces\IRenderable;

class CJSONRenderable implements \Framework\Interfaces\Irenderable
{
	protected $_content;

	/**
	 * Method intializes the CJSONRenderable with specific content.
	 * @param array $content The content to render.
	 */
	public function initialize($content)
	{
		$this->_content = $content;
	}

	/**
	 * Method renders the content to JSON
	 */
	public function render()
	{
		header("Content-Type: application/json");
		return json_encode($this->_content['render']);
	}
}
?>
