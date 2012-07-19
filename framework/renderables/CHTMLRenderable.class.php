<?
namespace Arbitrage2\Renderables;
use Arbitrage2\Interfaces\IRenderable;

class CHTMLRenderable implements IRenderable
{
	public function render($data=NULL)
	{
		return $this->_data;
	}
}
?>
