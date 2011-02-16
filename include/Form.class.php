<?
Class Form extends HTMLComponent
{
	private $_properties;
	private $_values;

	public function __construct($properties, $values = NULL)
	{
		$this->_populateObjectVariables($properties);
		$this->_values = $values;
		echo "<form id=\"{$this->id}\" name=\"{$this->id}\" method=\"{$this->method}\">\n";
	}

	public function __get($name)
	{
		if(isset($this->_properties[$name]))
			return $this->_properties[$name];

		return NULL;
	}

	public function text($id, $attribs=array())
	{
		$attribs = array_merge($attribs, array('value' => $this->_values[$id]));
		return HTMLComponent::inputText("{$this->id}_$id", $attribs);
	}

	public function select($id, $values, $default=array(), $attribs=array())
	{
		$d = $this->_values[$id];
		if(!is_array($d))
			$d = array($d);

		$default = array_merge($default, $d);
		return HTMLComponent::inputSelect("{$this->id}_$id", $values, $default, $attribs);
	}

	public function multiSelect($id, $values, $default=array(), $attribs=array())
	{
		$d = $this->_values[$id];
		if(!is_array($d))
			$d = array($d);

		$default = array_merge($default, $d);
		return HTMLComponent::inputMultiSelect("{$this->id}_$id", $values, $default, $attribs);
	}

	public function checkbox($id, $attribs=array())
	{
		$checked = $this->_values[$id];
		if($checked == true)
			$attribs = array_merge($attribs, array('checked' => 'checked'));

		return HTMLComponent::inputCheckbox("{$this->id}_$id", $attribs);
	}

	public function submit($id, $value, $attribs=array())
	{
		return HTMLComponent::submitButton("{$this->id}_$id", $value, $attribs);
	}

	public function hidden($id, $value=NULL, $attribs=array())
	{
		if($value == NULL)
			$value = $this->_values[$id];

		return parent::inputHidden("{$this->id}_$id", $value, $attribs);
	}

	public function endForm()
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
	}
}
?>
