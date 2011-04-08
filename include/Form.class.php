<?
Class Form extends HTMLComponent
{
	private $_properties;
	private $_values;

	public function __construct($properties, $values = NULL)
	{
		//check if $values is a model
		if(gettype($values) == "object" && is_subclass_of($values, 'Model'))
			$values = $values->toArray();
		
		$this->_populateObjectVariables($properties);
		$this->_values = $values;
		echo "<form id=\"{$this->id}\" name=\"{$this->id}\" method=\"{$this->method}\" action=\"{$this->action}\" enctype=\"{$this->enctype}\">\n";
	}

	public function __get($name)
	{
		if(isset($this->_properties[$name]))
			return $this->_properties[$name];

		return NULL;
	}

	public function text($id, $attribs=array())
	{
		$value   = $this->_getValue($id);
		$value   = ((isset($value))? $value: '');
		$attribs = array_merge($attribs, array('value' => $value));
		$id      = $this->_normalizeName($id);
		return HTMLComponent::inputText("{$this->id}_$id", $attribs);
	}

	public function select($id, $values, $default=array(), $attribs=array())
	{
		$d = $this->_getValue($id);
		if(!is_array($d))
			$d = array($d);

		$default = array_merge($default, $d);
		$id      = $this->_normalizeName($id);
		return HTMLComponent::inputSelect("{$this->id}_$id", $values, $default, $attribs);
	}

	public function multiSelect($id, $values, $default=array(), $attribs=array())
	{
		$d = $this->_getValue($id);
		if(!is_array($d))
			$d = array($d);

		$default = array_merge($default, $d);
		$id      = $this->_normalizeName($id);
		return HTMLComponent::inputMultiSelect("{$this->id}_$id", $values, $default, $attribs);
	}

	public function textArea($id, $value=NULL, $attribs=array())
	{
		$value   = (($value === NULL)? $this->_getValue($id) : $value);
		$id      = $this->_normalizeName($id);
		return HTMLComponent::inputTextArea("{$this->id}_$id", $value, $attribs);
	}

	public function file($id, $attribs=array())
	{
		$id = $this->_normalizeName($id);
		return HTMLComponent::inputFile("{$this->id}_$id", $attribs);
	}

	public function checkbox($id, $attribs=array())
	{
		$checked = $this->_getValue($id);
		if($checked == true)
			$attribs = array_merge($attribs, array('checked' => 'checked'));

		$id      = $this->_normalizeName($id);
		return HTMLComponent::inputCheckbox("{$this->id}_$id", $attribs);
	}

	public function submit($id, $value, $attribs=array())
	{
		return HTMLComponent::submitButton("{$this->id}_$id", $value, $attribs);
	}

	public function hidden($id, $value=NULL, $attribs=array())
	{
		if($value == NULL)
			$value = $this->_getValue($id);

		$id      = $this->_normalizeName($id);
		return parent::inputHidden("{$this->id}_$id", $value, $attribs);
	}

	public function end()
	{
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

		return $value;
	}
}
?>
