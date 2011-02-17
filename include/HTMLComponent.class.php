<?
class HTMLComponent extends Component
{
	public static function label($tag, $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html    = "<label $attribs>$tag</label>\n";

		return $html;
	}

	public static function inputText($id, $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"text\" id=\"$id\" name=\"$id\" $attribs />\n";

		return $html;
	}

	public static function inputSelect($id, $values, $selected=array(), $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html  = "<select name=\"$id\" id=\"$id\" $attribs>\n";

		foreach($values as $k=>$v)
		{
			$s = '';
			if(in_array($k, $selected))
				$s = "selected";
			$html .= "<option value=\"$k\" $s>$v</option>\n";
		}

		$html .= "</select>\n";
		
		return $html;
	}

	public static function inputMultiSelect($id, $values, $selected=array(), $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html  = "<select name=\"$id" . "[]\" id=\"$id\" multiple $attribs>\n";


		foreach($values as $k=>$v)
		{
			$s = '';
			if(in_array($k, $selected))
				$s = "selected";
			$html .= "<option value=\"$k\" $s>$v</option>\n";
		}

		$html .= "</select>\n";
		
		return $html;
	}

	public static function inputCheckbox($id, $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"checkbox\" id=\"$id\" name=\"$id\" $attribs />\n";

		return $html;
	}

	public static function submitButton($id, $value, $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"submit\" id=\"$id\" name=\"$id\" value=\"$value\" $attribs />\n";

		return $html;	
	}

	public static function inputButton($id, $value, $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html    = "<button id=\"$id\" name=\"$id\" $attribs>$value</button>\n";

		return $html;	
	}

	public static function inputHidden($id, $value, $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"hidden\" id=\"$id\" name=\"$id\" value=\"$value\" $attribs />\n";

		return $html;	
	}

	public static function inputTextArea($id, $value = "", $attribs=array())
	{
		$attribs = HTMLComponent::_generateAttribs($attribs);
		$html    = "<textarea name=\"$id\" id=\"$id\" $attribs >$value</textarea>\n";

		return $html;	
	}

	private function _generateAttribs($attribs)
	{
		$ret = '';
		foreach($attribs as $k=>$v)
			$ret .= "$k=\"$v\" ";

		return trim($ret);
	}
}
?>
