<?
namespace Framework\Form\Elements;

class CBaseFormElement implements \Framework\Interfaces\IFormElement
{
	protected $_attributes;
	protected $_value;
	protected $_id;

	public function __construct($id, $value, $attributes=array())
	{
		$this->_attributes = $attributes;
		$this->_id         = $id;

		//Set value
		$this->setValue($value);
	}

	/**
	 * Method that auto loads form elements.
	 * @param $class_name The class name to load.
	 */
	static public function autoLoad($class_name)
	{
		if(preg_match('/FormElement$/i', $class_name))
		{
			$namespace = \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($class_name);
			$ret       = \Framework\Base\CKernel::getInstance()->requireFile($namespace, false);

			//Throw exception if unable to load form
			if(!$ret)
				throw new \Framework\Exceptions\EArbitrageException("Unable to load form element '$namespace'.");
		}
	}

	/**
	 * Method converts class to HTML tag.
	 */
	public function __toString()
	{
		return $this->_toString();
	}

	/**
	 * Method sets the value of the element.
	 * @param mixed $value The value to set the element to.
	 */
	public function setValue($value)
	{
		if($value instanceof \Framework\Form\Elements\CBaseFormElement)
			$value = $this->_value->getValue();

		$this->_value = $value;
	}
	
	/**
	 * Method gets the element id.
	 * @return Returns the id.
	 */
	public function getID()
	{
		return $this->_id;
	}

	/**
	 * Method gets the value of the element.
	 * @return mixed Retrusn the value of the element.
	 */
	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * Method returns the arbitrage path for the element minus the form name.
	 * @return Returns the arbitrage path.
	 */
	public function getElementArbitragePath()
	{
		$path = preg_replace('/^[^\[]*(.*)$/', '$1', $this->_id);
		$path = preg_replace('/\[/', '.', $path);
		$path = preg_replace('/\]/', '', $path);
		$path = preg_replace('/^\./', '', $path);

		return $path;
	}

	/**
	 * Method returns the type of element the class is.
	 * @return Returns the type of element this is.
	 */
	protected function _getType()
	{
		$class  = preg_replace('/.*\\\([^\\\]+)$/', '$1', get_called_class());
		$class  = preg_replace('/C(.*)FormElement/i', '$1', $class);
		$class  = strtolower($class);

		return $class;
	}

	/**
	 * Method returns the HTML string of this element.
	 * @return Returns the HTML string of the element.
	 */
	protected function _toString()
	{
		//Get method to call
		$type   = $this->_getType();
		$method = "input$type";

		//Set attribs
		$attribs          = $this->_attributes;
		$attribs['value'] = $this->getValue();

		return \Framework\DOM\CDomGenerator::$method($this->_id, $attribs);
	}
}

spl_autoload_register('\Framework\Form\Elements\CBaseFormElement::autoLoad', true);
?>
