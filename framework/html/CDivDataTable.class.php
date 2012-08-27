<?
namespace Framework\HTML;

class CHTMLDivDataTable extends \Framework\HTML\CHTMLDataTable
{
	public function render()
	{
		$attrs = CHTMLComponent::generateAttribs($this->_attrs);
		$html  = "<div id=\"{$this->_id}\" $attrs>";
			$html .= $this->_renderHeader();
			$html .= $this->_renderData();
		$html .= "</div>";

		die("IMPLEMENT ME");
	}

	protected function _renderHeader()
	{
		$html = '<div class="header">';
		foreach($this->_headers as $title => $val)
			$html = '<div class="entry">' . $val . '</div>';

		$html .= '</div>';

		return $html;
	}

	protected function _renderData()
	{
		$html = '<div class="dataset">';
			
		//Go thorugh each entry
		foreach($this->_data as $entry)
		{
			//Add cell
			foreach($this->_headers as $key => $val)
			{
				$html .= "<div class=\"cell\">";
				if(gettype($val) === "string")
				{
					$arr = new CArrayObject($entry);
					$val = $this->_normalizeValue($arr->xpath($val));
				}
				else($val instanceof IHTMLDataTableEntry)
					$val = $val->render($this, $entry);

				$html .= "<div class=\"cell\">$val</div>";
			}

			$html .= "</div>";
		}
		
		$html .= "</div>";
	}
}
?>
