<?
namespace Framework\HTML\Types;

class CImageType implements \Framework\Interfaces\IHTMLDataTableType
{
	private $_path;
	private $_default;

	public function __construct($path, $default)
	{
		//set variables
		$this->_path    = "/" . preg_replace('/\./', '/', $path);
		$this->_default = $default;
	}

	public function render(\Framework\Interfaces\IHTMLDataTable $table, $entry)
	{
		if(is_array($entry))
			$arr = new \Framework\Utils\CArrayObject($entry);
		else if($entry instanceof \Framework\Model\CModel)
			$arr = $entry;
		else
			throw new \Exception("Unable to handle!");

		$val = $arr->apath($this->_path);
		if($val == NULL)
			$val = $this->_default;

		return "<img src=\"$val\" />";
	}
}
?>
