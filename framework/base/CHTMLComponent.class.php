<?
class CHTMLComponent /*extends Component*/
{
	public static function label($tag, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<label $attribs>$tag</label>\n";

		return $html;
	}

	public static function createForm($id, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<form name=\"$id\">\n";

		return $html;
	}

	public static function endForm()
	{
		return "</form>\n";
	}

	public static function inputText($id, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"text\" id=\"$id\" name=\"$id\" $attribs />\n";

		return $html;
	}

	public static function inputSelect($id, $values, $attribs=array(), $selected=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html  = "<select name=\"$id\" id=\"$id\" $attribs>\n";

		if(count($values))
		{
			foreach($values as $k=>$v)
			{
				$s = '';
				if(in_array($k, $selected))
					$s = "selected";
				$html .= "<option value=\"$k\" $s>$v</option>\n";
			}
		}

		$html .= "</select>\n";
		
		return $html;
	}

	public static function inputMultiSelect($id, $values, $attribs=array(), $selected=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html  = "<select name=\"$id" . "[]\" id=\"$id\" multiple=\"multiple\" $attribs>\n";

		foreach($values as $key=>$value)
		{
			//Check if avalue is array, if so it is in an optgroup
			if(is_array($value))
			{
				$html .= "<optgroup label=\"$key\">\n";
				foreach($value as $k=>$v)
				{
					$s = '';
					if(in_array((string) $k, $selected, true))
						$s = "selected=\"selected\"";

					$html .= "<option value=\"$k\" $s>$v</option>\n";
				}

				$html .= "</optgroup>\n";
			}
			else
			{
				$s = '';
				if(in_array((string) $key, $selected, true))
					$s = "selected=\"selected\"";

				$html .= "<option value=\"$key\" $s>$value</option>\n";
			}
		}

		$html .= "</select>\n";
		return $html;
	}

	public static function inputStateSelector($id, $attribs=array(), $selected=array())
	{
		$states  = States::getNames();
		$abbr    = States::getAbbreviations();
		foreach($states as &$s)
			$s = ucwords($s);

		$states = array_combine($abbr, $states);
		$states = array_merge(array('-' => "Select a State"), $states);

		return self::inputSelect($id, $states, $attribs, $selected);
	}

	public static function inputCheckbox($id, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"checkbox\" id=\"$id\" name=\"$id\" $attribs />\n";

		return $html;
	}

	public static function inputRadio($id, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"radio\" id=\"$id\" name=\"$id\" $attribs />\n";

		return $html;
	}

	public static function submitButton($id, $value, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"submit\" id=\"$id\" name=\"$id\" value=\"$value\" $attribs />\n";

		return $html;	
	}
        
	public static function imageSubmitButton($id, $value, $src, $attribs=array())
        {
                $attribs = CHTMLComponent::_generateAttribs($attribs);
                $html    = "<input type=\"image\" id=\"$id\" name=\"$id\" value=\"$value\" src=\"$src\" alt=\"\" $attribs />\n";

                return $html;
        }

	public static function inputButton($id, $value, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<button id=\"$id\" name=\"$id\" $attribs>$value</button>\n";

		return $html;	
	}

	public static function inputHidden($id, $value, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"hidden\" id=\"$id\" name=\"$id\" value=\"$value\" $attribs />\n";

		return $html;	
	}

	public static function inputTextArea($id, $value = "", $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<textarea name=\"$id\" id=\"$id\" $attribs >$value</textarea>\n";

		return $html;	
	}

	public static function inputFile($id, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<input type=\"file\" name=\"$id\" id=\"$id\" />\n";

		return $html;
	}

	public static function image($id, $src, $attribs=array())
	{
		$attribs = CHTMLComponent::_generateAttribs($attribs);
		$html    = "<img name=\"$id\" id=\"$id\" src=\"$src\" $attribs />\n";

		return $html;
	}

	public static function generateLink($tag, $url, $attribs = NULL)
	{
		$url = new URL($url);
		$a   = "";

		if($attribs != NULL)
		{
			foreach($attribs as $k=>$v)
				$a .= " $k=\"$v\"";
		}

		$href = "<a href=\"" . $url->getURL() . "\" $a>$tag</a>";
		return $href;
	}

	public static function arrayToInput($type, $name, $arr)
	{
		$html = "";

		//Generate 
		$arr  = array($name => $arr);
		$ret  = self::toArrayNotationString($arr);
		$func = "input$type";

		foreach($ret as $key=>$value)
			$html .= self::$func($key, $value);

		return $html;
	}

	public static function toArrayNotationString($vars, $pre="")
	{
		$query = array();
		foreach($vars as $key=>$value)
		{
			if($pre != "")
				$key = "$pre" . "[" . $key . "]";

			if(is_array($value) && count($value))
				$query = array_merge($query, self::toArrayNotationString($value, $key));
			elseif(is_array($value))  //empty array
				$query = array_merge($query, array($key => array()));
			else
				$query = array_merge($query, array($key => $value));
		}

		return $query;

	}

	private static function _generateAttribs($attribs)
	{
		$ret = '';
		if(count($attribs))
		{
			foreach($attribs as $k=>$v)
				$ret .= "$k=\"$v\" ";
		}

		return trim($ret);
	}
}
?>
