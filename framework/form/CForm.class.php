<?
namespace Framework\Forms;
use \Framework\DOM\CDomGenerator;

Class CForm extends CFormModel
{
	protected $_values;
	private $_attributes;
	private $_prepend_name;
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
		$properties          = \Framework\Utils\CArrayObject::mergeArray($defaults, $properties);
		$this->_attributes   = $properties['attributes'];
		$this->_values       = $properties['values'];
		$this->_prepend_name = false;
		
		//Normalize name
		$this->_attributes['id'] = preg_replace('/\\\/', '_', $this->_attributes['id']);

		//Get value and create a CFormMOdel
		$values        = (($this->_values === NULL)? array() : (($this->_values instanceof \Framework\Interfaces\IModel)? $this->_values->toArray() : $this->_values));
		$this->_model  = (($this->_values instanceof \Framework\Interfaces\IModel)? get_class($this->_values) : NULL);
		$this->_values = new CFormModel($values);
	}

	static public function autoLoad($class_name)
	{
		if(preg_match('/Form$/', $class_name))
		{
			$namespace = \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($class_name);
			$ret       = \Framework\Base\CKernel::getInstance()->requireFile($namespace, false);

			//Throw exception if unable to load form
			if(!$ret)
				throw new \Framework\Exceptions\EArbitrageException("Unable to load form '$namespace'.");
		}
	}

	static public function getSubmittedForm($class="Framework.Forms.CForm")
	{
		$controller = \Framework\Base\CKernel::getInstance()->getApplication()->getController();
		$request    = $controller->getRequest();
		if(!empty($request->_form))
		{
			//Parse form properties
			list($vals, $model) = self::_parseFormProperties(preg_replace('/\-form$/', '', $request->_form), $request);

			//Create model if the form was attached to one
			if($model !== NULL)
				$vals = new $model($vals);

			//Convert to PHP
			$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($class);

			return new CSubmittedForm(new $class(array('values' => $vals)));
		}

		return NULL;
	}

	static private function _parseFormProperties($form_id, $properties)
	{
		$vals  = array();
		$model = NULL;

		//Get properties
		foreach($properties as $form=>$entries)
		{
			if($form_id == $form)
			{
				$vals = $entries;
				break;
			}
			
			/*if(preg_match('/^' . $form_id . '_/i', $key) && $key !== "{$form_id}__model")
				$vals[preg_replace('/^' . $form_id . '_/i', '', $key)] = $val;
			elseif($key === "{$form_id}__model")
				$model = $val;*/
		}

		return array($vals, $model);
	}

	/*public function getModel()
	{
		if(!isset($this->_model))
			return NULL;

		$class = $this->_model;
		return new $class($this->_values);
	}*/

	/**
	 * Method converts form values to a Model.
	 * @param string $opt_namespace The namespace where the Model resides.
	 * @returns Returns the model.
	 */
	public function convertToDatabaseModel($opt_namespace="")
	{
		//If namespace is not set, use internal namespace
		if($opt_namespace == "")
		{
			die("CForm::convertToDatabaseModel");
		}

		//Get class
		$class  = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($opt_namespace);
		$values = $this->_values;

		//Convert values to model values
		return $this->_values->convertToDatabaseModel($opt_namespace);
	}

	/*public function toArray()
	{
		return $this->_values->toArray();
	}

	public function __get($name)
	{
		$arr = new \Framework\Utils\CArrayObject($this->_values);
		return $arr->$name;
	}*/

	/**
	 * Method starts the form
	 */
	public function start()
	{
		$attrs = "";
		foreach($this->_attributes as $key => $val)
			$attrs .= "$key=\"$val\" ";

		$attrs = trim($attrs);
		echo "<form $attrs>\n";
	}

	public function label($label, $attribs=array())
	{
		if(!empty($attribs['for']))
			$attribs['for'] = $this->_normalizeName($attribs['for']);

		return CDomGenerator::labelTag($label, $attribs);
	}

	public function text($id, $attribs=array())
	{
		$value   = $this->_getValue($id);
		$value   = ((isset($value))? $value: '');
		$attribs = array_merge($attribs, array('value' => $value));
		$id      = $this->_normalizeName($id);
		return CDomGenerator::inputText($this->_prependFormID($id), $attribs);
	}

	public function password($id, $attribs=array())
	{
		$value   = $this->_getValue($id);
		$value   = ((isset($value))? $value: '');
		$attribs = array_merge($attribs, array('value' => $value));
		$id      = $this->_normalizeName($id);
		return CDomGenerator::inputPassword($this->_prependFormID($id), $attribs);
	}

	public function select($id, $values, $attribs=array(), $default=array())
	{
		$d = $this->_getValue($id);
		if(!is_array($d))
			$d = array($d);

		$default = array_merge($default, $d);
		$id      = $this->_normalizeName($id);
		return CDomGenerator::inputSelect($this->_prependFormID($id), $values, $attribs, $default);
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
		return CDomGenerator::inputMultiSelect($this->_prependFormID($id), $values, $attribs, $selected);
	}

	public function selectState($id, $attribs=array(), $default=array())
	{
		$default = ((count($default) == 0)? $this->_getValue($id) : $default);
		$id = $this->_normalizeName($id);
		return CDomGenerator::inputStateSelector($id, $attribs, $default);
	}

	public function textArea($id, $value=NULL, $attribs=array())
	{
		$value   = (($value === NULL)? $this->_getValue($id) : $value);
		$id      = $this->_normalizeName($id);
		return CDomGenerator::inputTextArea($this->_prependFormID($id), $value, $attribs);
	}

	public function file($id, $attribs=array())
	{
		$value   = $this->_getValue($id);
		$value   = ((isset($value))? $value: '');
		$attribs = array_merge($attribs, array('value' => $value));
		$id      = $this->_normalizeName($id);

		return CDomGenerator::inputFile($this->_prependFormID($id), $attribs);
	}

	public function checkbox($id, $attribs=array())
	{
		$checked = $this->_getValue($id);
		if($checked == true)
			$attribs = array_merge($attribs, array('checked' => 'checked'));

		$id = $this->_normalizeName($id);
		return CDomGenerator::inputCheckbox($this->_prependFormID($id), $attribs);
	}

	public function button($id, $value, $attribs=array())
	{
		return CDomGenerator::inputButton($this->_prependFormID($id), $value, $attribs);
	}

	public function submit($id, $value, $attribs=array())
	{
		return CDomGenerator::submitButton($this->_prependFormID($id), $value, $attribs);
	}
	
	public function imageSubmit($id, $valid, $src, $attribs=array())
	{
		return CDomGenerator::imageSubmitButton($this->_prependFormID($id), $value, $src, $attribs);
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
			//TODO: Recode to use idVal
			die("CForm::end -- code idVal");
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
		$key   = explode(".", $name);
		$value = "";
		for($i=0; $i<count($key); $i++)
			$value .= "[{$key[$i]}]";

		//Prepend form name
		$value = "{$this->_attributes['id']}$value";

		return $value;
	}

	private function _prependFormID($id)
	{
		return (($this->_prepend_name)? "{$this->_attributes['id']}_$id" : $id);
	}
}

//Register autoload for form
spl_autoload_register('\Framework\Forms\CForm::autoLoad', true);
?>
