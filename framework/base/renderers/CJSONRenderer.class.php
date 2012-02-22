<?
class CJSONRenderer extends CRenderer implements ITextRenderer
{
	public function render($content)
	{
		header("Content-type: application/json");
		return json_encode($content);
	}
}
?>
