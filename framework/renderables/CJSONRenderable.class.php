<?
namespace Framework\Renderables;
use \Framework\Interfaces\IRenderable;

class CJSONRenderable implements IRenderable
{
	public function render($data=NULL)
	{
		header("Content-Type: application/json");
		return json_encode($data);
	}
}
?>
