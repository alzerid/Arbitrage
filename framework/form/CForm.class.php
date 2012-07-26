<?
namespace Framework\Forms;
use \Arbitrage2\HTML\CHTMLComponent;

Class CForm extends CHTMLComponent
{
	protected $_values;
	private $_attributes;
	private $_model;

	public function __construct($properties=array())
	{
		//Set default attributes
		$defaults = array('attributes' => array('id'           => 'form-noname',
		                                        'method'       => 'POST',
		                                        'enctype'      => 'application/x-www-form-urlencoded',
		                                        'autocomplete' => 'on'),
		                  'values' => array());


		//Merge properties
		$properties = self::_mergeProperties($defaults, $properties);

		$this->_values     = $properties['values'];
		$this->_attributes = $properties['attributes'];

		//check if $values is a model
		if($this->_values instanceof CModel)
		{
			$this->_model  = get_class($this->_values);
			$this->_values = $this->_values->toArray();
		}
		elseif($this->_values instanceof \Arbitrage2\Model2\CModel)
			$this->_model = get_class($this->_values);
		elseif($this->_values === NULL)
			$this->_values = array();
	}

	static public function autoLoad($class_name)
	{
		if(preg_match('/Form$/', $class_name))
		{
			$path = CApplication::getConfig()->_internals->approotpath . "app/forms/$class_name.php";
			if(!file_exists($path))
				throw new EArbitrageException("Form '$class_name' does not exist.");

			require_once($path);
		}
	}

	static public function getSubmittedForm($class="CForm")
	{
		$controller = CApplication::getInstance()->getController();
		$request    = $controller->getRequest();
		if(isset($request['_form']))
		{
			//Parse form properties
			list($vals, $model) = self::_parseFormProperties(preg_replace('/\-form$/', '', $request['_form']), $request);

			if($model !== NULL)
				$vals = new $model($vals);

			return new CSubmittedForm(new $class(array('values' => $vals)));
		}

		return NULL;
	}

	static protected function _mergeProperties(array &$array1, &$array2=NULL)
	{
		$merged = $array1;
		if(is_array($array2))
		{
			foreach($array2 as $key=>$val)
			{
				if(!isset($merged[$key]))
					$merged[$key] = array();

				if(is_array($array2[$key]))
					$merged[$key] = is_array($merged[$key]) ? self::_mergeProperties($merged[$key], $array2[$key]) : $array2[$key];
				else
					$merged[$key] = $val;
			}
		}

		return $merged;
	}

	static private function _parseFormProperties($form_id, $properties)
	{
		$vals  = array();
		$model = NULL;

		foreach($properties as $key=>$val)
		{
			if(preg_match('/^' . $form_id . '_/i', $key) && $key !== "{$form_id}__model")
				$vals[preg_replace('/^' . $form_id . '_/i', '', $key)] = $val;
			elseif($key === "{$form_id}__model")
				$model = $val;
		}

		return array($vals, $model);
	}

	public function getModel()
	{
		if(!isset($this->_model))
			return NULL;

		$class = $this->_model;
		return new $class($this->_values);
	}

	public function toArray()
	{
		return $this->_values;
	}

	public function submitted()
	{
	}

	public function __get($name)
	{
		$arr = new CArrayObject($this->_values);
		return $arr->$name;
	}

	public function start()
	{
		$attrs = "";
		foreach($this->_attributes as $key => $val)
			$attrs .= "$key=\"$val\" ";

		$attrs = trim($attrs);
		echo "<form $attrs>\n";
	}

	public function text($id, $attribs=array())
	{
		$value   = $this->_getValue($id);
		$value   = ((isset($value))? $value: '');
		$attribs = array_merge($attribs, array('value' => $value));
		$id      = $this->_normalizeName($id);
		return CHTMLComponent::inputText($this->_prependFormID($id), $attribs);
	}

	public function password($id, $attribs=array())
	{
		$value   = $this->_getValue($id);
		$value   = ((isset($value))? $value: '');
		$attribs = array_merge($attribs, array('value' => $value));
		$id      = $this->_normalizeName($id);
		return CHTMLComponent::inputPassword($this->_prependFormID($id), $attribs);
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
		$value   = $this->_getValue($id);
		$value   = ((isset($value))? $value: '');
		$attribs = array_merge($attribs, array('value' => $value));
		$id      = $this->_normalizeName($id);

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

	public function button($id, $value, $attribs=array())
	{
		return CHTMLComponent::inputButton($this->_prependFormID($id), $value, $attribs);
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
		{
			echo $this->hidden('_id', (string) $this->_values['_id']);
			echo $this->hidden('_model', $this->_model);
		}

		echo "<input type=\"hidden\" name=\"_form\" id=\"_form\" value=\"{$this->_attributes['id']}-form\" />\n";
		echo "</form>\n";
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
		$value = "{$this->_attributes['id']}_$value";

		return $value;
	}

	private function _prependFormID($id)
	{
		return (($this->_prepend_name)? "{$this->_attributes['id']}_$id" : $id);
	}
}
?>
