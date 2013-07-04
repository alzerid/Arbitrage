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

	public function __toString()
	{
		return $this->_toString();
	}

	public function render()
	{
		$attrs = \Framework\DOM\CDOMGenerator::generateAttribs($this->_attrs);
		$html  = "<table id=\"{$this->_id}\" $attrs>";
			$html .= $this->_renderHeader();
			$html .= $this->_renderData();
		$html .= "</table>";

		return $html;
	}

	protected function _renderHeader()
	{
		$html  = "<thead>";
			$html .= "<tr>";

				foreach($this->_headers as $title => $val)
					$html .= "<th>$title</th>";
				
			$html .= "</tr>";
		$html .= "</thead>";

		return $html;
	}

	protected function _renderData()
	{
		$html  = "<tbody>";

		//TODO: Can we check for countable object? --EMJ
		$count = (($this->_data instanceof \Iterator)? $this->_data->count() : count($this->_data));

		if($count>0)
		{
			foreach($this->_data as $entry)
			{
				$html .= "<tr>";

				foreach($this->_headers as $key => $val)
				{
					//Get value
					if($val instanceof \Framework\Interfaces\IHTMLDataTableType)
						$val = $val->render($this, $entry);
					else
						$val = $this->_normalizeValue($entry->apath($val));

					//Check if empty
					if(empty($val))
						$val = "&nbsp;";

					$html .= '<td>' . $val . '</td>';
				}

				$html .= "</tr>";
			}
		}
		else
			$html .= "<tr class=\"empty\"><td colspan=\"" . count($this->_headers) . "\">There are no records.</td></tr>";

		$html .= "</tbody>";

		return $html;
	}

	protected function _toString()
	{
		return $this->render();
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
