<?
namespace Framework\HTML\Types;

class CImageType implements \Framework\Interfaces\IHTMLDataTableType
{
	private $_apath;
	private $_default;

	public function __construct($apath, $default)
	{
		//set variables
		$this->_apath   = $apath;
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

		$val = $arr->apath($this->_apath);
		if($val == NULL)
			$val = $this->_default;

		return "<img src=\"$val\" />";
	}
}
?>
