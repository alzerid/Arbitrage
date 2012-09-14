<?
namespace Framework\Form\Elements;

class CCheckBoxFormElement extends \Framework\Form\Elements\CBaseFormElement
{
	/**
	 * Overloaded method that ensures value is a boolean.
	 * @param $value The value to set the checkbox to.
	 */
	public function setValue($value)
	{
		//Ensures boolean
		if($value === NULL)
			$this->_value = false;
		elseif(is_string($value))
		{
			$value = strtolower($value);
			if($value === "on")
				$this->_value = true;
			elseif($value === "off")
				$this->_value = false;
			else
				throw new \Framework\Exceptions\EFormException("Cannot assign value '$value' to combo box.");
		}
		elseif(is_bool($value))
			$this->_value = $value;
		else
			throw new \Framework\Exceptions\EFormException("Cannot assign value '$value' to combo box.");

		parent::setValue($value);
	}

	/**
	 * Method returns the HTML string of this element.
	 * @return Returns the HTML string of the element.
	 */
	protected function _toString()
	{
		if($this->getValue())
			$this->_attributes['checked'] = 'checked';

		return \Framework\DOM\CDomGenerator::inputCheckbox($this->_id, $this->_attributes);
	}

}
?>
