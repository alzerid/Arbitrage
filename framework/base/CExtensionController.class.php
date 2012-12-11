<?
namespace Framework\Base;
use \Framework\Base\CController;

class CExtensionController extends CController
{
	public function addJavaScriptTag($tag)
	{
		parent::addJavaScriptTag("/extensions" . $tag);
	}
}
?>
