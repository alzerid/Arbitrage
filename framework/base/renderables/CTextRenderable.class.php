<?
class CTextRenderable implements IRenderable
{
	private $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	public function render()
	{
		header("Content-Type: text/plain");
		return $this->_data;
	}
}
?>
