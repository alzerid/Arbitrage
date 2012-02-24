<?
class CFrameworkController extends CBaseController
{
	public function render($file, $_vars=NULL)
	{
		if($_vars === NULL)
			$_vars = array();

		//Extract variables
		extract($_vars);

		//Generate file path
		$path = CApplication::getConfig()->_internals->fwrootpath . "framework/views/$file.php";

		ob_start();
		require_once($path);
		$content = ob_get_clean();

		return $content;
	}

	public function renderInternal(IRenderer $renderer)
	{
	}
}
?>
