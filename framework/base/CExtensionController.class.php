<?
namespace Arbitrage2\Base;
use \Arbitrage2\Base\CController;

class CExtensionController extends CController
{
	public function addJavaScriptTag($tag)
	{
		parent::addJavaScriptTag("/extensions" . $tag);
	}
}
?>
