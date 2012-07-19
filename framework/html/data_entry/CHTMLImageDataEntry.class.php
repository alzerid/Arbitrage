<?
class CHTMLImageDataEntry implements IHTMLDataTableEntry
{
	private $_path;
	private $_default;

	public function __construct($path, $default)
	{
		//set variables
		$this->_path    = "/" . preg_replace('/\./', '/', $path);
		$this->_default = $default;
	}

	public function render(IHTMLDataTable $table, array $entry)
	{
		$arr = new CArrayObject($entry);
		$val = $arr->xpath($this->_path);
		if($val == NULL)
			$val = $this->_default;

		return "<img src=\"$val\" />";
	}
}
?>
