<?
class CJavascriptRenderable implements IRenderable
{
	private $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	public function render()
	{
		header("Content-Type: application/javascript");
		return $this->_data;
	}
}
?>
