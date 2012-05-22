<?
class CHTMLRenderable implements IRenderable
{
	private $_data;

	public function __construct($html)
	{
		$this->_data = $html;
	}

	public function render()
	{
		return $this->_data;
	}
}
?>
