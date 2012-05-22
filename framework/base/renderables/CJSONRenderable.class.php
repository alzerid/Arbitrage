<?
class CJSONRenderable implements IRenderable
{
	private $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	public function render()
	{
		header("Content-Type: application/json");
		return json_encode($this->_data);
	}
}
?>
