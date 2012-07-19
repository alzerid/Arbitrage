<?
namespace Arbitrage2\Forms;
use \Arbitrage2\Forms\CForm;

class CSubmittedForm
{
	private $form;

	public function __construct(CForm $form)
	{
		$this->_form = $form;
		$this->_form->submitted();
	}
	
	public function __get($name)
	{
		return $this->_form->$name;
	}

	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->_form, $name), $arguments);
	}
}
?>
