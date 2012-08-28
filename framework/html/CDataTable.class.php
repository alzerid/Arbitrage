<?
namespace Framework\HTML;

abstract class CDataTable implements \Framework\Interfaces\IHTMLDataTable
{
	protected $_headers;
	protected $_data;
	protected $_attrs;
	protected $_id;

	public function __construct($id, $headers, $data, $attrs=array())
	{
		//Set variables
		$this->_data    = $data;
		$this->_attrs   = $attrs;
		$this->_id      = $id;
		$this->_headers = $headers;

		//Add datatable class
		if(empty($this->_attrs['class']))
			$this->_attrs['class'] = "datatable";
		else
			$this->_attrs['class'] = ' datatable';
	}

	abstract public function render();

	protected function _normalizeValue($val)
	{
		switch(gettype($val))
		{
			case "boolean":
				$val = (($val)? "true" : "false");

		}

		return $val;
	}
}
?>
