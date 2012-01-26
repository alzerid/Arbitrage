<?
//Require any Arbitrage libraries here
//Application::requireLibrary('api/Babel.class.php');

//Require any application libraries here
//require_once('app/lib/BreadCrumbs.class.php');
//require_once('app/lib/SessionObject.class.php');
define('TRACKER_URL', 'http://tracker.apartmentfetch.com/?');

class ApplicationController extends Controller
{
	protected $_title;        //Title
	protected $_description;  //Meta tag description
	protected $_canonical;    //Canonical tag
	protected $_config;       //Config object

	public function __construct($controller, $action)
	{
		$this->_title        = "TITLE";
		$this->_description  = "DESCRIPTION";
		$this->_canonical    = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";

		parent::__construct($controller, $action);

		$this->_config = Application::getConfig();
	}

	public function filters()
	{
		//Add any filter code. If there is none, remove this method
		$filters = array();
		
		return $filters;
	}

	public function getTitle()
	{
		return $this->_title;
	}

	public function getMetaDescription()
	{
		return $this->_description;
	}

	public function getCanonical()
	{
		return $this->_canonical;
	}

	public function includeControllerJavascript($controller=NULL)
	{
		if($controller == NULL)
			$controller = $this->_controller_name;

		//Include controller if
		$js = ((Application::getConfig()->application['use_minification'])? "/cjavascript/{$controller}-min.js" : "/cjavascript/{$controller}.js");

		//Add inline js for controller creation
		Application::includeJavascriptFile($js);
	}

	public function includeEnvironmentStylesheetFile($style)
	{
		if($this->_config->application['use_minification'])
			$style = preg_replace('/(.*)\.css/', '$1-min.css', $style);

		$this->includeStylesheetFile($style);
	}

	public function generateEnvironmentJavascriptLink($js)
	{
		if($this->_config->application['use_minification'])
			$js = preg_replace('/(.*)\.js/', '$1-min.js', $js);

		return Application::generateJavascriptLink($js);
	}

	public function includeEnvironmentJavascriptFile($js)
	{
		if($this->_config->application['use_minification'])
			$js = preg_replace('/(.*)\.js/', '$1-min.js', $js);

		$this->includeJavascriptFile($js);
	}

	public function getWebsite()
	{
		return "TradeStars.com";
	}
}
?>
