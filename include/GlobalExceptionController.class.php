<?
class GlobalExceptionController extends Controller
{
	protected $_view;
	protected $_ex;

	public function errorAction()
	{
		//Clean out buffer
		ob_end_clean();

		//Set view path
		$this->setViewPath(Application::getConfig()->fwrootpath . 'template/views/');
		return array('render' => '_global/error', 'layout' => 'error', 'variables' => array('ex' => $this->_ex));
	}

	public function setException($ex)
	{
		$this->_ex = $ex;
	}
}
?>
