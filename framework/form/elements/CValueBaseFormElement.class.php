<?
namespace Framework\Form\Elements;

class CValueBaseFormElement extends \Framework\Form\Elements\CBaseFormElement
{
	/**
	 * Method returns the HTML string of this element.
	 * @return Returns the HTML string of the element.
	 */
	protected function _toString()
	{
		$type   = $this->_getType();
		$method = "input$type";
		return \Framework\DOM\CDOMGenerator::$method($this->_id, $this->getValue(), $this->_attributes);
	}
}
?>
