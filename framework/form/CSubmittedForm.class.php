<?
namespace Framework\Forms;

class CSubmittedForm
{
	private $form;

	public function __construct(\Framework\Forms\CForm $form)
	{
		$this->_form = $form;
	}
	
	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->_form, $name), $arguments);
	}
}
?>
