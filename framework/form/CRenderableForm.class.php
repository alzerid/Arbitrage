<?
namespace Framework\Form;

//TODO: Normalize values (checkbox on off to true false)

Class CRenderableForm extends \Framework\Form\CForm implements \Framework\Interfaces\IViewFileRenderableContext
{
	private $_initialized;
	private $_file;

	public function __construct($render, array $attributes=array())
	{
		//Call parent constructor
		parent::__construct($attributes);

		//Set file and variables
		$this->_values      = new \Framework\Model\CModel;
		$this->_initialized = false;
		$this->_file        = $render;

		//Transfrom _vaolues into CTypedFormModel
		$this->_initializeModel();
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
	 * Method renders the view file associated with the Form object.
	 */
	public function render()
	{
		//Require renderable
		\Framework\Base\CKernel::getInstance()->getApplication()->requireRenderable('Framework.Renderables.CViewFilePartialRenderable');

		//Create renderer and render
		$renderer = new \Framework\Renderables\CViewFilePartialRenderable;
		$renderer->initialize($this->getRenderPath(), array('render' => $this->_file));
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
	 * Method populates elements and initializes the model
	 */
	protected function _initializeModel()
	{
		ob_start();
		$this->render();
		ob_clean();
		
		//Create model
		$this->_values      = \Framework\Forms\CFormModel::instantiate($this->_values);
		$this->_initialized = true;
	}

	/**
	 * Method creates HTML Form Element Objects.
	 */
	protected function _createElement($name, $args)
	{
		//TODO: Return object from model if already initialized

		$element = parent::_createElement($name, $args);
		if(!$this->_initialized)
		{
			$key = $element->getElementArbitragePath();
			$this->_values->setAPathValue($key, $element);
		}

		return $element;
	}
}
?>
