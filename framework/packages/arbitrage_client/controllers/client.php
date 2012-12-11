<?
namespace Framework\Packages\ArbitrageClient\Controllers;

class ClientController extends \Framework\Base\CJavascriptController
{
	public function bootstrapAction()
	{
		//Return config
		$config = $this->getPackage()->getConfig()->toArray();
		$config = array_merge($config, array('debug' => $this->_application->getConfig()->arbitrage2->debugMode));

		//Check include paths
		if(!isset($config['includePaths']))
			$config['includePaths'] = array();

		//Generate include paths
		$config['includePaths'] = array_merge($config['includePaths'], $this->getPackage()->getIncludePaths());

		//Get JSON
		$config = json_encode($config);
		$js     = "var arbitrage2 = { config: $config };";

		return array('render' => $js);
	}

}
?>
