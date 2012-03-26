<?
Class CForm extends CHTMLComponent
{
	private $_properties;
	private $_values;
	private $_model;

	public function __construct($properties, $values=NULL, $model=NULL)
	{
		//check if $values is a model
		$this->_model = $model;
		if($values instanceof CModel)
		{
			$this->_model = get_class($values);
			$values       = $values->toArray();
		}
		
		$this->_populateObjectVariables($properties);
		$this->_values = $values;
	}

	static public function getForm($arr, $frm)
	{
		$vals  = array();
		$model = NULL;
		foreach($arr as $key=>$val)
		{
			if(preg_match('/^' . $frm . '_/i', $key) && $key !== "{$frm}__model")
				$vals[preg_replace('/^' . $frm . '_/i', '', $key)] = $val;
			elseif($key === "{$frm}__model")
				$model = $val;
		}

		if(count($vals))
			return new CForm(array(), $vals, $model);

		return NULL;
	}

	public function getModel()
	{
		if(!isset($this->_model))
			return NULL;

		$class = $this->_model;
		return new $class($this->_values);
	}

	public function __get($name)
	{
		if(isset($this->_properties[$name]))
			return $this->_properties[$name];

		return NULL;
	}

	public function start()
	{
		echo "<form id=\"{$this->id}\" name=\"{$this->id}\" method=\"{$this->method}\" action=\"{$this->action}\" enctype=\"{$this->enctype}\">\n";
	}

	public function text($id, $attribs=array())
	{
		$value   = $this->_getValue($id);
		$value   = ((isset($value))? $value: '');
		$attribs = array_merge($attribs, array('value' => $value));
		$id      = $this->_normalizeName($id);
		return CHTMLComponent::inputText($this->_prependFormID($id), $attribs);
	}

	public function select($id, $values, $attribs=array(), $default=array())
	{
		$d = $this->_getValue($id);
		if(!is_array($d))
			$d = array($d);

		$default = array_merge($default, $d);
		$id      = $this->_normalizeName($id);
		return CHTMLComponent::inputSelect($this->_prependFormID($id), $values, $attribs, $default);
	}

	public function multiSelect($id, $values, $attribs=array(), $default=array())
	{
		$s = $this->_getValue($id);
		if(isset($s))
		{
			if(!is_array($s))
				$s = array($s);

			$selected = $s;
		}
		else
			$selected = $default;

		$id      = $this->_normalizeName($id);
		return CHTMLComponent::inputMultiSelect($this->_prependFormID($id), $values, $attribs, $selected);
	}

	public function selectState($id, $attribs=array(), $default=array())
	{
		$default = ((count($default) == 0)? $this->_getValue($id) : $default);
		$id = $this->_normalizeName($id);
		return CHTMLComponent::inputStateSelector($id, $attribs, $default);
	}

	public function textArea($id, $value=NULL, $attribs=array())
	{
		$value   = (($value === NULL)? $this->_getValue($id) : $value);
		$id      = $this->_normalizeName($id);
		return CHTMLComponent::inputTextArea($this->_prependFormID($id), $value, $attribs);
	}

	public function file($id, $attribs=array())
	{
		$id = $this->_normalizeName($id);
		return CHTMLComponent::inputFile($this->_prependFormID($id), $attribs);
	}

	public function checkbox($id, $attribs=array())
	{
		$checked = $this->_getValue($id);
		if($checked == true)
			$attribs = array_merge($attribs, array('checked' => 'checked'));

		$id = $this->_normalizeName($id);
		return CHTMLComponent::inputCheckbox($this->_prependFormID($id), $attribs);
	}

	public function submit($id, $value, $attribs=array())
	{
		return CHTMLComponent::submitButton($this->_prependFormID($id), $value, $attribs);
	}
	
	public function imageSubmit($id, $valid, $src, $attribs=array())
	{
		return CHTMLComponent::imageSubmitButton($this->_prependFormID($id), $value, $src, $attribs);
	}

	public function hidden($id, $value=NULL, $attribs=array())
	{
		if($value == NULL)
			$value = $this->_getValue($id);

		$id = $this->_normalizeName($id);
		return parent::inputHidden($this->_prependFormID($id), $value, $attribs);
	}

	public function end()
	{
		if(isset($this->_model))
			echo "<input type=\"hidden\" name=\"{$this->id}__model\" id=\"{$this->id}__model\" value=\"{$this->_model}\" />\n";

		echo "<input type=\"hidden\" name=\"_form\" id=\"_form\" value=\"{$this->id}-form\" />\n";
		echo "</form>\n";
	}

	protected function _populateObjectVariables($vars)
	{
		foreach($vars as $k=>$v)
			$this->_properties[$k] = $v;
		
		if($this->id == NULL)
			$this->_properties['id'] = "form-noname";

		if($this->method == NULL)
			$this->_properties['method'] = 'POST';
		
		if($this->action == NULL)
			$this->_properties['action'] = '';

		if($this->enctype == NULL)
			$this->_properties['enctype'] = 'application/x-www-form-urlencoded';
	}

	private function _getValue($id)
	{
		//Dot notation to array notation
		$key = explode(".", $id);
		if(count($key) > 1)
		{
			$value = $this->_values;
			for($i=0; $i<count($key); $i++)
			{
				if(isset($value[$key[$i]]))
					$value = $value[$key[$i]];
				else
					$value = NULL;
			}
		}
		else
		{
			if(isset($this->_values[$key[0]]))
				$value = $this->_values[$key[0]];
			else
				$value = NULL;
		}

		return $value;
	}

	private function _normalizeName($name)
	{
		//Dot notation to array notation
		$key = explode(".", $name);
		if(count($key) > 1)
		{
			$value = $key[0];
			for($i=1; $i<count($key); $i++)
				$value .= "[{$key[$i]}]";
		}
		else
			$value = $name;

		//Prepend form name
		$value = "{$this->id}_$value";

		return $value;
	}

	private function _prependFormID($id)
	{
		return (($this->_prepend_name)? "{$this->id}_$id" : $id);
	}
}
?>