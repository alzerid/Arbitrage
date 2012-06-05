<?
class CErrorController extends CController
{
	private $_err_vars;
	private $_err_file;

	public function __construct($file, $_vars=array())
	{
		parent::__construct();
		$this->_err_vars = $_vars;
		$this->_err_file = $file;
	}

	public function processAction()
	{
		if($this->_err_vars === NULL)
			$this->_err_vars = array();

		//Check for application files
		$file = $this->_err_file;
		$path = CApplication::getConfig()->_internals->approotpath . "app/views/_internal/errors/$file.php";
		if(!file_exists($path))  //set viewpath to framework
			$this->setViewPath(CApplication::getConfig()->_internals->fwrootpath . "framework/views/");
		else
		{
			$this->setViewPath(CApplication::getConfig()->_internals->approotpath . "app/views/_internal/");
			$this->setLayoutPath(CApplication::getConfig()->_internals->approotpath . "app/views/_internal/errors/");
			$this->setDefaultLayout('layout');
		}


		return array('render' => "errors/$file", 'variables' => $this->_err_vars);
	}
}

?>
