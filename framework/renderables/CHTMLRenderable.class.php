<?
namespace Framework\Renderables;
use Framework\Interfaces\IRenderable;

class CHTMLRenderable implements IRenderable
{
	public function render($data=NULL)
	{
		return $this->_data;
	}
}
?>
