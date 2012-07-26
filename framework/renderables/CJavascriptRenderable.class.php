<?
namespace Framework\Renderables;

class CJavascriptRenderable implements \Framework\Interfaces\IContentRenderable
{
	protected $_content;

	public function initialize($content)
	{
		$this->_content = $content;
	}

	public function render()
	{
		ob_start();
		ob_implicit_flush(false);
		header("Content-Type: application/javascript");

		echo $this->_content['render'];

		return ob_get_clean();
	}
}
?>
