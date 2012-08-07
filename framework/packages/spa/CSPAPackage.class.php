<?
namespace Framework\Packages\SPA;

class CSPAPackage extends \Framework\Base\CWebPackage
{
	public function initialize()
	{
		$url = $this->getURL();

		//This package depends on Framework.ArbitrageClient
		$this->depends('Framework.Packages.ArbitrageClient');

		//Initialize package
		parent::initialize();

		//Add config route
		$this->addRoute("/^\/" . preg_replace('/\//', '\/', $url) . "\/javascript\/config.js.*$/i", "/$url/spa/config");

		//Initialize JS routes
		$this->initializeJavascript('spa');

		//Add include path
		$this->getApplication()->getPackage('Framework.Packages.ArbitrageClient')->addIncludePath('spa', "/$url");

		//Add javascript SPA
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => "/$url/javascript/spa.js"));
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => "/$url/javascript/config.js?action=" . $this->getApplication()->getVirtualURI()));
	}
}
?>
