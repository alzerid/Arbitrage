<?
class CErrorController extends CController
{
	public function render($file, $_vars=NULL)
	{
		if($_vars === NULL)
			$_vars = array();

		//Extract variables
		extract($_vars);

		//Check for application files
		$path = CApplication::getConfig()->_internals->approotpath . "app/views/_internal/errors/$file.php";
		if(file_exists($path))
			$content = parent::renderFile("_internal/errors/$file", 'error/default', $_vars);
		else
		{
			$path = CApplication::getConfig()->_internals->fwrootpath . "framework/views/$file.php"; //Get framework view

			ob_start();
			require_once($path);
			$content = ob_get_clean();
		}

		return $content;
	}

	public function renderInternal(IRenderer $renderer)
	{
	}
}

?>
