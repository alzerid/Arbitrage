<?
namespace Framework\Base;
use \Framework\Interfaces\IController;
use \Framework\Utils\CArrayObject;
use \Framework\Utils\CFlashPropertyObject;
use \Framework\Exceptions\EWebApplicationException;
use \Framework\Exceptions\EHTTPException;

abstract class CController implements IController
{
	protected $_get;              //Get variable
	protected $_post;             //Post variable
	protected $_cookie;           //Cookie variable
	protected $_request;          //Get and Post combination
	protected $_session;          //Session
	protected $_files;            //List of files uploaded
	protected $_flash;            //Flash variables
	protected $_view_variables;   //View variables that will passed into the view
	protected $_application;      //The application the controller belongs to
	protected $_package;          //The package the controller belongs to

	private $_ajax;         //Flag that checks if the controller is ajax
	private $_action;       //The action object that executes the action within this controller
	private $_namespace;    //Namespace of the controller
	private $_renderable;   //Type of renderables being used for this controller (default is CViewFileRenderable)
	private $_layout;       //Layout associated with the view (used only for CViewFileRenderable)
	private $_chain;        //Filter chain object

	/**
	 * Constructor initializes the controller.
	 */
	protected function __construct()
	{
		//Get files
		$files   = ((isset($_FILES))? $_FILES : array());
		$session = ((isset($_SESSION))? $_SESSION : array());

		//Setup PHP variables
		$this->_get            = new CArrayObject($_GET);
		$this->_post           = new CArrayObject($_POST);
		$this->_request        = new CArrayObject($_REQUEST);
		$this->_session        = new CArrayObject($session);
		$this->_files          = new CArrayObject($files);
		$this->_cookie         = new CArrayObject($_COOKIE);
		$this->_view_variables = new CArrayObject();
		$this->_chain          = new CFilterChain($this);
		$this->_flash          = NULL;

		//Set controller variables
		$this->_namespace = CKernel::getInstance()->convertPHPNamespaceToArbitrage(get_class($this));
		$this->_ajax      = preg_match('/AjaxController$/i', $this->_namespace);
		$this->_action    = NULL;
		$this->_layout    = 'default';
		$this->setRenderable('Framework.Renderables.CViewFileRenderable');  //Set default renderable
	}

	/**
	 * Static method creates a CController and assigns an application to it.
	 * @param \Framework\Base\CApplication $application The application to assign this controller with.
	 * @param \Framework\Base\CPackage $package The package to assign this controller with.
	 * @return \Framework\Base\CController An instance of CController.
	 */
	static public function createController(\Framework\Base\CApplication $application, \Framework\Base\CPackage $package=NULL)
	{
		$class      = get_called_class();
		$controller = new $class;

		//Initialize
		$controller->_application = $application;
		$controller->_package     = $package;
		$controller->initialize();

		return $controller;
	}

	/**
	 * Method that initializes the controller.
	 */
	public function initialize()
	{
		/* NOOP */
	}

	/**
	 * Method returns an array for filters.
	 * @return array Returns an empty array.
	 */
	public function filters()
	{
		return array();
	}

	/**
	 * Method executes the action/controller/view.
	 * return array Returns the contents from the executed action.
	 */
	public function execute()
	{
		if($this->_action == NULL)
		{
			if(CWebApplication::currentInstance()->getConfig()->arbitrage2->debugMode)
				throw new EWebApplicationException("Action not set in controller!");
			else
				throw new EHTTPException(EHTTPException::$HTTP_BAD_REQUEST);
		}

		//Run before filter
		$this->_chain->runFilter('before_filter');

		//Setup flash variables
		$this->_flash = new CFlashPropertyObject();

		//Execute the action
		$ret = $this->_action->execute();

		//Add flash variable to session
		$this->_flash->update();

		//Run the after filter
		$this->_chain->runFilter('after_filter', $ret);

		return $ret;
	}

	/**
	 * Sets the action to execute for this controller
	 * @param \CArbitrage2\Base\CAction $action The action to set to.
	 */
	public function setAction($action)
	{
		$this->_action = $action;
	}

	/**
	 * Method sets the renderable to use from the Actions return.
	 * @param string $renderable The arbitrage namespace representation of the renderable to use.
	 */
	public function setRenderable($renderable)
	{
		$this->_renderable = $renderable;
	}

	/**
	 * Method starts the PHP session.
	 */
	public function startSession()
	{
		//Ensure session has NOT started
		if(!isset($_SESSION))
		{
			session_start();
			$this->_session = new CArrayObject($_SESSION);
		}
	}

	/**
	 * Returns if this controller is an Ajax controller.
	 * @return boolean Returns true if this is an Ajax controller.
	 */
	public function isAjax()
	{
		return $this->_ajax;
	}

	/**
	 * Method sets the layout for CViewFileRenderable.
	 * @param string $layout The layout to set to.
	 */
	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	/**
	 * Method returns the currently set layout.
	 * @return Returns the currently set layout.
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * Returns the name of this controller.
	 * @return string Returns the name of the controller.
	 */
	public function getName()
	{
		$name = explode('.', $this->_namespace);
		$name = $name[count($name)-1];
		return strtolower(preg_replace('/(Ajax)?Controller$/i', '', $name));
	}

	/**
	 * Returns the action object.
	 * @return \Framework\Base\CAction Returns the CAction associated with this controller.
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * Method returns the package assigned to the controller.
	 * @return \Framework\Base\CPackage Returns the package the controller is associated with.
	 */
	public function getPackage()
	{
		return $this->_package;
	}

	/**
	 * Method returns the application assigned to the controller.
	 * @return \Framework\Base\CApplication Returns the application the controller is associated with.
	 */
	public function getApplication()
	{
		return $this->_application;
	}

	/**
	 * Method returns the $_REQUEST in CArrayObject format.
	 * @return \Framework\Utils\CArrayObject Returns the $_REQUEST.
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * Renders a partial using a view file.
	 * @param string $file The view file to render
	 * @param array $variables A key value array.
	 */
	public function renderPartial($file, array $variables=array())
	{
		static $renderable = NULL;
		if($renderable == NULL)
		{
			$renderable = $this->requireRenderable('Framework.Renderables.CViewFilePartialRenderable');
			var_dump($renderable);
			die('$controller->renderPartial');
		}
	}

	/**
	 * Method redirects to another url.
	 * @param string $namespace The namespace to redirect to.
	 * @param array $parameters Parameters to pass.
	 */
	public function redirect($namespace, $parameters=array())
	{
		$url = "/" . \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToURL($namespace);
		if(count($parameters))
		{
			$params = http_build_query($parameters);
			$url   .= "?$params";
		}

		//Redirect URL
		$this->redirectURL($url);
	}

	/**
	 * Method redirects to another url.
	 * @param string $namespace The namespace to redirect to.
	 */
	public function redirectURL($url)
	{
		header("Location: $url");
		die();
	}


	/**
	 * Method forwards execution to another controller specified by Arbitrage namespace.
	 * @param string $namespace The namespace of the Controller/Action to forward the execution to.
	 * @param array $opt_variables The variables to pass to the constructor.
	 */
	public function forward($namespace, $opt_variables=array())
	{
		//Forward instructions
		return $this->_application->forward($namespace, $opt_variables);
	}

	/**
	 * Render the return from an Action to a renderbable.
	 * @param $content Either an array or IRenderable.
	 * @param boolean $opt_return Determines if the render should return or echo out the rendered results.
	 */
	public function render($content, $opt_return=false)
	{
		ob_start();

		$out = NULL;
		if($content instanceof \Framework\Interfaces\IRenderable)
			$out = $content->render();
		elseif(is_array($content))
		{
			//Create the renderable object
			$this->requireRenderable($this->_renderable);

			//Create renderable
			$class      = CKernel::getInstance()->convertArbitrageNamespaceToPHP($this->_renderable);
			$renderable = new $class;

			if($renderable instanceof \Framework\Renderables\CViewFilePartialRenderable || $renderable instanceof \Arbtirage2\Interfaces\IViewFileRenderable)
			{
				$path = preg_replace('/(controllers|ajax).*$/i', 'views', CKernel::getInstance()->convertArbitrageNamespaceToPath($this->_namespace));
				$path = $this->_application->getPath() . "/$path";

				//Setup default render
				if(!isset($content['render']))
					$content['render'] = $this->getName() . "/" . $this->getAction()->getName();

				//Add _controller and _application
				!isset($content['variables']) && $content['variables'] = array();
				$content['variables'] = array_merge($content['variables'], array('_controller' => $this, '_application' => $this->_application));

				//Initialize
				$renderable->initialize($path, $content);
			}
			else
				$renderable->initialize($content);

			//Check renderable type, if it is of type CViewFileRenderable, set layouts
			if($renderable instanceof \Framework\Interfaces\ILayoutRenderable)
				$renderable->setLayout($this->_layout);

			//Render the renderable
			$out = $renderable->render();
		}
			
		//Handle out
		if($out === NULL)
		{
			if($this->_application->getConfig()->arbitrage2->debugMode)
				throw new EWebApplicationException('Content is NULL. Check your renderable type and your return in your action.');
			else
				throw new EHTTPException(EHTTPException::$HTTP_INTERNAL_ERROR);
		}

		//After filter
		$this->_chain->runFilter('post_process', $out);

		//Check to see if we print out the view or not
		if(!$opt_return)
			echo $out;

		ob_end_flush();

		return $out;
	}

	/**
	 * Proxy function that calls CWebApplication::requireRenderable
	 * @param string $namespace The arbitrage namespace where the renderable object resides.
	 * @throws \Framework\Exceptions\EWebApplicationException
	 */
	public function requireRenderable($namespace)
	{
		$this->_application->requireRenderable($namespace);
	}
}
?>
