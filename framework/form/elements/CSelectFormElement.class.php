<?
namespace Framework\Form\Elements;

class CSelectFormElement extends \Framework\Form\Elements\CBaseFormElement
{
	protected $_options;

	public function __construct($id, $options, $attributes=array(), $defaults=array())
	{
		$this->_options = $options;
		parent::__construct($id, $defaults, $attributes);
	}

	/**
	 * Overloaded method that ensures a selected item is in the $options.
	 * @param $value The value to set the select to.
	 */
	public function setValue($value)
	{
		if(!is_array($value))
			$value = array($value);
		elseif($value instanceof \Framework\Form\Elements\CSelectFormElement)
		{
			$this->setOptions($value->_options);
			$value = $value->getValue();
		}

		//Ensure $value is part of $options
		$options = array_keys($this->_options);
		foreach($value as $val)
		{
			if(!in_array($val, $options))
				throw new \Framework\Exceptions\EFormException("Select value '$val' is not in enum.");
		}

		//Call parent
		parent::setValue($value);
	}

	/**
	 * Method clears the options list.
	 */
	public function clearOptions()
	{
		$this->_options = array();
	}

	/**
	 * Method sets the options.
	 * @param array $options The options to set.
	 */
	public function setOptions(array $options)
	{
		$this->_options = $options;
	}

	/**
	 * Method adds an option.
	 * @param $value The value of the object.
	 * @param $text The text to display.
	 */
	public function addOption($value, $text)
	{
		$this->_options[$value] = $text;
	}

	/**
	 * Method removes an option.
	 * @param $value The value of the object.
	 */
	public function removeOption($value, $text)
	{
		unset($this->_options[$value]);
	}

	/**
	 * Method returns the HTML string of this element.
	 * @return Returns the HTML string of the element.
	 */
	protected function _toString()
	{
		return \Framework\DOM\CDomGenerator::inputSelect($this->_id, $this->_options, $this->_attributes, $this->getValue());
	}
}
?>
