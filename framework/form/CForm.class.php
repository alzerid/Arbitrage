<?
namespace Framework\Form;
use \Framework\DOM\CDomGenerator;

Class CForm
{
	static protected $_CUSTOM = array();
	protected $_attributes;
	protected $_values; 

	public function __construct(array $attributes=array())
	{
		static $dattributes = array('id'           => 'form-noname',
		                            'method'       => 'POST',
		                            'enctype'      => 'application/x-www-form-urlencoded');
		                            /*'autocomplete' => 'on');*/

		//Set attributes and value
		$this->_attributes = \Framework\Utils\CArrayObject::mergeArray($dattributes, $attributes);
		$this->_values     = new \Framework\Model\CModel;

		//Normalize id
		$this->_attributes['id'] = preg_replace('/\\\/', '_', $this->_attributes['id']);
	}

	/**
	 * Method auto loads any class that contains Form$.
	 * @param $class_name The class name to load.
	 */
	static public function autoLoad($class_name)
	{
		if(preg_match('/Form$/', $class_name))
		{
			$namespace = \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($class_name);
			$ret       = \Framework\Base\CKernel::getInstance()->requireFile($namespace, false);

			//Throw exception if unable to load form
			if(!$ret)
				throw new \Framework\Exceptions\EFormException("Unable to load form '$namespace'.");
		}
	}

	/**
	 * Method converts the form namespace to arbitrage namespace.
	 * @param $namespace The namespace to convert.
	 * @return Returns the Arbitrage namespace representation.
	 */
	static public function convertFormNamespaceToArbitrage($namespace)
	{
		$name     = preg_replace('/^([^\[]*).*/', '$1', $namespace);
		$notation = str_replace($name, '', $namespace);
		$notation = preg_replace('/\[/', '.', $notation);
		$notation = preg_replace('/\]/', '', $notation);
		$name     = preg_replace('/_/', '.', $name);

		return $name . $notation;
	}

	/**
	 * Method adds tag element.
	 * @param $name The tag name to use.
	 * @param $class The class to use.
	 */
	static public function addCustomElementTag($name, $class)
	{
		self::$_CUSTOM[$name] = $class;
	}

	/**
	 * Magic method that intercepts to create tags.
	 */
	public function __call($name, $args)
	{
		return $this->_createElement($name, $args);
	}

	/**
	 * Method returns the model associated with this form.
	 * @return \Framework\Forms\CFormModel The model associated with this form.
	 */
	public function getModel()
	{
		return $this->_values;
	}

	/**
	 * Method returns the attribute value.
	 * @param $key The attribute to retreive the value for.
	 * @return Returns the value of the attribute or NULL.
	 */
	public function getAttribute($key)
	{
		return (($this->_attributes !== NULL && array_key_exists($key, $this->_attributes))? $this->_attributes[$key] : NULL);
	}

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

	public function multiSelect($id, $values, $attribs=array(), $default=array())
	{
		die(__METHOD__);
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

	/*public function selectState($id, $attribs=array(), $default=array())
	{
		$default = ((count($default) == 0)? $this->_getValue($id) : $default);
		$id = $this->_normalizeName($id);
		return CDomGenerator::inputStateSelector($id, $attribs, $default);
	}

	public function submit($id, $value, $attribs=array())
	{
		return CDomGenerator::submitButton($this->_prependFormID($id), $value, $attribs);
	}
	
	public function imageSubmit($id, $valid, $src, $attribs=array())
	{
		return CDomGenerator::imageSubmitButton($this->_prependFormID($id), $value, $src, $attribs);
	}*/

	public function end()
	{
		echo "<input type=\"hidden\" name=\"_form\" id=\"_form\" value=\"{$this->_attributes['id']}-form\" />\n";
		echo "</form>\n";
	}

	/**
	 * Method creates HTML Form Element Objects.
	 */
	protected function _createElement($name, $args)
	{
		$inputs = array('text', 'password', 'checkbox', 'submit', 'reset', 'file');
		$valued = array('hidden', 'textarea', 'button');

		$name  = strtolower($name);
		if(in_array($name, $inputs))
		{
			//Get parameters
			$id      = $args[0];
			$attribs = (!(array_key_exists(1, $args))? array() : $args[1]);
			$class   = "\\Framework\\Form\\Elements\\C" . ucwords($name) . "FormElement";

			//Create element and return
			return new $class($this->_normalizeName($id), $this->_getValue($id, $attribs), $attribs);
		}
		elseif(in_array($name, $valued))
		{
			//Get parameters
			$id      = $args[0];
			$value   = (!(array_key_exists(1, $args))? NULL : $args[1]);
			$attribs = (!(array_key_exists(2, $args))? array() : $args[2]);
			$class   = "\\Framework\\Form\\Elements\\C" . ucwords($name) . "FormElement";

			return new $class($this->_normalizeName($id), $this->_getValue($id, $attribs, $value), $attribs);
		}
		elseif($name === "select")
		{
			//Get parameters
			$id       = $args[0];
			$options  = (!(array_key_exists(1, $args))? array('') : $args[1]);
			$attribs  = (!(array_key_exists(2, $args))? array() : $args[2]);
			$selected = (!(array_key_exists(3, $args))? array('') : $args[3]);

			//Create element and return
			return new \Framework\Form\Elements\CSelectFormElement($this->_normalizeName($id), $options, $attribs, $this->_getValue($id, $attribs), $attribs);
		}
		elseif(array_key_exists(strtolower($name), self::$_CUSTOM))
		{
			$id    = $args[0];
			$args  = array_slice($args, 1);
			$class = self::$_CUSTOM[strtolower($name)];

			//Create element and return
			return new $class($this->_normalizeName($id), $this->_getValue($id), $args);
		}

		throw new \Framework\Exceptions\EFormException("Unknown element '$name'.");
	}

	/**
	 * Method normalized the name from an arbitrage path to [] form notatoin.
	 * @param $name The arbitrage path name to convert.
	 */
	protected function _normalizeName($name)
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

	/** 
	 * Method returns the proper value by order.
	 */
	private function _getValue($id, $attribs=NULL, $val=NULL)
	{
		//Get model first
		$aval = $this->getModel()->apath($id);
		if($aval !== NULL)
			return $aval;

		//get attribs
		if(!empty($attribs['value']))
			return $attribs['value'];

		if(!empty($attribs['checked']))
			return $attribs['checked'];

		return $val;
	}
}

//Register autoload for form
spl_autoload_register('\Framework\Form\CForm::autoLoad', true);
?>
