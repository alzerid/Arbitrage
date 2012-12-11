<?
namespace Framework\Base;
use \Framework\Base\CBaseController;

class CFrameworkController extends CBaseController
{
	public function renderContent($file, $_vars=NULL)
	{
		if($_vars === NULL)
			$_vars = array();

		//Extract variables
		extract($_vars);

		//Generate file path
		$path = CApplication::getConfig()->_internals->fwrootpath . "framework/views/$file.php";

		ob_start();
		require($path);
		$content = ob_get_clean();

		return $content;
	}
}
?>
