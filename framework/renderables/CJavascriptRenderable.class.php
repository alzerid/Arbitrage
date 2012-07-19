<?
namespace Arbitrage2\Renderables;
use \Arbitrage2\Interfaces\IRenderable;

class CJavascriptRenderable implements IRenderable
{
	public function render($data=NULL)
	{
		ob_start();
		ob_implicit_flush(false);
		header("Content-Type: application/javascript");

		echo $data['data'];

		return ob_get_clean();
	}
}
?>
