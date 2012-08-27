<?
namespace Framework\HTML;

class CDataTable implements \Framework\Interfaces\IHTMLDataTable
{
	protected $_headers;
	protected $_data;
	protected $_attrs;
	protected $_id;

	public function __construct($id, $headers, $data, $attrs=array())
	{
		//Set variables
		$this->_data  = $data;
		$this->_attrs = $attrs;
		$this->_id    = $id;

		//Normalize headers
		$new = array();
		foreach($headers as $key => $val)
		{
			if(is_string($val))
			{
				$val = preg_replace('/\./', '/', $val);
				$val = "/$val";
			}

			$new[$key] = $val;
		}

		$this->_headers = $new;
	}

	public function render()
	{
		$attrs = CHTMLComponent::generateAttribs($this->_attrs);
		$html  = "<table id=\"{$this->_id}\" $attrs>";

		$html .= $this->_renderHeader();
		$html .= $this->_renderData();

		$html .= "</table>";

		return $html;
	}
	
	protected function _renderHeader()
	{
		$html = "<thead><tr>";
		foreach($this->_headers as $title => $val)
			$html .= "<th>$title</th>";

		$html .= "</tr></thead>";

		return $html;
	}

	protected function _renderData()
	{
		$html = "<tbody>";

		foreach($this->_data as $entry)
		{
			$html .= "<tr>";

			foreach($this->_headers as $key => $val)
			{
				//Check what val type
				if(gettype($val) === "string")
				{
					$arr = new CArrayObject($entry);
					$val = $this->_normalizeValue($arr->xpath($val));
				}
				elseif($val instanceof \Framework\Interfaces\IHTMLDataTableEntry)
					$val = $val->render($this, $entry);

				$html .= "<td>$val</td>";
			}

			$html .= "</tr>";
		}

		$html .= "</tbody>";

		return $html;
	}

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
