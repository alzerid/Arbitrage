<?
namespace Framework\Form;

Class CRenderableForm extends \Framework\Form\CForm implements \Framework\Interfaces\IViewFileRenderableContext
{
	private $_initialized;
	private $_removed;
	private $_file;
	private $_view;

	public function __construct($render, array $attributes=array())
	{
		//Call parent constructor
		parent::__construct($attributes);

		//Set file and variables
		$this->_values      = new \Framework\Model\CModel;
		$this->_initialized = false;
		$this->_file        = $render;
		$this->_removed     = array();
		$this->_view        = new \Framework\Utils\CArrayObject;

		//Transfrom _vaolues into CTypedFormModel
		$this->_initializeModel();
	}

	/**
	 * Method returns the form in string format.
	 * @return string Returns the form.
	 */
	public function __toString()
	{
		return $this->_toString();
	}

	/**
	 * Method returns the form file to render.
	 * @return Returnst the file to render.
	 */
	public function getFile()
	{
		return $this->_file;
	}

	/**
	 * Method removes an element from rendering.
	 * @param $namespace The arbitrage path to the element.
	 */
	public function removeElement($element)
	{
		$this->_removed[] = $element;
	}

	/**
	 * Method returns the path to the file to render.
	 * @return string Returns a string to the path of the file.
	 */
	public function getRenderPath()
	{
		//Create path and render file
		$namespace = \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage(get_called_class());
		
		//Add view
		$path = \Framework\Base\CKernel::getInstance()->getApplication()->getViewPathFromArbitrageNamespace($namespace);
		$path = preg_replace('/\/forms\//', '/_forms/', $path);
		$path = explode('/', $path);

		//Normalize file
		$file = array_splice($path, -1);
		$file = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2' , $file[0]));

		//Implode
		$path = implode('/', $path);

		return $path;
	}

	/**
	 * Returns the view variable object.
	 * @return \Framework\Utils\CArrayObject
	 */
	public function getViewVariable()
	{
		return $this->_view;
	}

	/**
	 * Method renders the view file associated with the Form object.
	 */
	public function render()
	{
		//Require renderable
		\Framework\Base\CKernel::getInstance()->getApplication()->requireRenderable('Framework.Renderables.CViewFilePartialRenderable');

		//Get view variables if there are any
		$view = $this->_view->toArray();

		//Create renderer and render
		$renderer = new \Framework\Renderables\CViewFilePartialRenderable;
		$renderer->initialize($this->getRenderPath(), array('render' => $this->_file, 'variables' => $view));
		$renderer->setContext($this);

		return $renderer->render();
	}

	/**
	 * Method renders something partially
	 */
	public function renderPartial($file, $_vars=NULL)
	{
		$renderer = new \Framework\Renderables\CViewFilePartialRenderable;
		$renderer->initialize($this->getRenderPath(), array('render' => $file, 'variables' => $_vars));
		$renderer->setContext($this);

		return $renderer->render();
	}

	/**
	 * Method that implements the \Framework\Interfaces\IViewFileRenderableContext interface.
	 * @param string $file The file to render.
	 * @param array $_vars Variables to extract and pass to the view file.
	 */
	public function renderContext($file, $_vars=NULL)
	{
		//Extract _vars
		//$_vars = array_merge($this->_view->toArray(), $_vars);
		extract($_vars);

		//Require the file
		require($file);
	}

	/**
	 * Method creates a subform.
	 * $param $name The name of the subform to associate to.
	 * @param $class The class of the subform.
	 * @param $options Any options to send to the renderable form.
	 * @return Returns the subform.
	 */
	public function subform($name, $class, $options=array())
	{
		if(!$this->_initialized)
		{
			//Ensure class is subclsas of \Framework\Form\CRenderableForm
			$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($class);
			if(!is_subclass_of($class, "\\Framework\\Form\\CRenderableForm", true))
				throw new \Framework\Exceptions\EFormException("subform '$class' must be of inherit \\Framework\\Form\\CRenderableForm");

			//Create new form
			$element = new $class($name, $this, $options);
			$this->_values->setAPathValue($name, $element);
		}
		else
			$element = $this->_values->getElement($name);

		return $element;
	}

	/**
	 * Method populates elements and initializes the model
	 */
	protected function _initializeModel()
	{
		ob_start();
		$this->render();
		ob_clean();
		
		//Create model
		$this->_values      = new \Framework\Form\CFormModel($this->_values);
		$this->_initialized = true;
	}

	/**
	 * Method creates HTML Form Element Objects.
	 */
	protected function _createElement($name, $args)
	{
		//TODO: Return object from model if already initialized

		if(!$this->_initialized)
		{
			//Create element
			$element = parent::_createElement($name, $args);
			$key     = $element->getElementArbitragePath();

			//Set arbitrage path value
			$this->_values->setAPathValue($key, $element);
		}
		else
		{
			//Get element
			$id = ((empty($args[0]))? "" : $args[0]);
			if(in_array($id, $this->_removed))
				return NULL;

			//Create element
			$id      = $args[0];
			$element = $this->_values->getElement($id);
		}

		return $element;
	}

	/**
	 * Method returns the initialized flag value.
	 * @return boolean The initalized flag is returned.
	 */
	protected function _getInitialized()
	{
		return $this->_initialized;
	}

	/**
	 * Method sets the form state to the initialize state.
	 */
	protected function _startInitialize()
	{
		$this->_initialized = false;
	}

	/**
	 * Method sets the form state to the endinitialize state.
	 */
	public function _endInitialize()
	{
		$this->_initialized = true;
	}

	/**
	 * Method that can be overridden that converts the object to a string.
	 * return string Returns the string representing the object.
	 */
	protected function _toString()
	{
		return $this->render();
	}
}
?>
