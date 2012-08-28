<?
namespace Framework\HTML;

class CDivDataTable extends \Framework\HTML\CDataTable
{
	public function render()
	{
		$attrs = \Framework\DOM\CDOMGenerator::generateAttribs($this->_attrs);
		$html  = "<div id=\"{$this->_id}\" $attrs>";
			$html .= $this->_renderHeader();
			$html .= $this->_renderData();
		$html .= "</div>";

		return $html;
	}

	protected function _renderHeader()
	{
		$html = '<div class="header">';
		foreach($this->_headers as $title => $val)
			$html .= '<div class="entry">' . $title. '</div>';

		$html .= '</div>';

		return $html;
	}

	protected function _renderData()
	{
		$html = '<div class="data">';
		if(count($this->_data) > 0)
		{
			//Go thorugh each entry
			foreach($this->_data as $entry)
			{
				$html .= '<div class="row">';

				//Add cell
				foreach($this->_headers as $key => $val)
				{
					//Get value
					if($val instanceof \Framework\Interfaces\IHTMLDataTableType)
						$val = $val->render($this, $entry);
					else
						$val = $this->_normalizeValue($entry->apath($val));

					$html .= '<div class="entry">' . $val . '</div>';
				}

				$html .= "</div>";
			}
		}
		else
			$html .= '<div class="row empty">There are no records.</div>';

		
		$html .= "</div>";

		return $html;
	}
}
?>
