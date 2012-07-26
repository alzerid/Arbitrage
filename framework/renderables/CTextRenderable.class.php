<?
namespace Framework\Renderables;
use \Arbitrage2\Interfaces\IRenderable;

class CTextRenderable implements IRenderable
{
	public function render($data=NULL)
	{
		header("Content-Type: text/plain");
		return $data;
	}
}
?>
