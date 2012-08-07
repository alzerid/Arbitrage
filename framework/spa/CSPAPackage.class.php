<?
namespace Framework\SPA;

class CSPAPackage extends \Framework\Base\CPackage
{
	public function initialize()
	{
		//This package depends on Framework.ArbitrageClient
		$this->depends('Framework.ArbitrageClient');

		//Initialize package
		parent::initialize();

		//Initialize JS routes
		parent::initializeJavascript('spa');

		//Add bootstrap.js and arbitrage javascript tags
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/framework/spa/javascript/config.js'));
		\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => '/framework/spa/javascript/spa.js'));

		//require javascript file defined by user
		/*$namespace = preg_replace('/^\/?([^\/]+)\/?.*$/', '$1' , $this->getApplication()->getVirtualURI());
		$path      = $this->getConfig()->includePaths[$namespace];
		if(isset($path))
		{
			$path = $path . "/" . $namespace . "/application.js";
			$path = preg_replace('/[\\/]+/', '/', $path); //Remove double '/'
			\Framework\DOM\CDOMGenerator::addJavascriptTag(array('src' => $path));
		}

		//Add include path to package
		$this->getApplication()->getPackage('Framework.ArbitrageClient')->addIncludePath('spa', '/framework/spa/javascript');*/
	}
}
?>
