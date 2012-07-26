<?
namespace Framework\Renderables;
use \Framework\Interfaces\IRenderable;

class CTextRenderable implements IRenderable
{
	public function render($data=NULL)
	{
		header("Content-Type: text/plain");
		return $data;
	}
}
?>
