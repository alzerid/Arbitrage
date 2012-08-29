<?
namespace Framework\Forms;

Class CRenderableForm extends CForm implements \Framework\Interfaces\IViewFileRenderableContext
{
	protected $_view;
	private $_file;

	public function __construct($properties=array())
	{
		/*if(empty($properties['render']))
			throw new \Framework\Exceptions\EArbitrageException("Form render property not defined!");*/

		//If render is not set, create the string
		if(empty($properties['render']))
		{
			$properties['render'] = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', preg_replace('/.*\\\([^\\\]*)$/', '$1', get_called_class()));
			$properties['render'] = strtolower(preg_replace('/ /', '_', $properties['render']));
		}

		//Set file and variables
		$this->_file = $properties['render'];

		//Create view variables etc
		$vars        = array('variables' => ((!empty($properties['variables']))? $properties['variables'] : array()));
		$this->_view = new \Framework\Utils\CArrayObject($vars);

		parent::__construct($properties);
	}

	/**
	 * Method renders the view file associated with the Form object.
	 */
	public function render()
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

		//Require renderable
		\Framework\Base\CKernel::getInstance()->getApplication()->requireRenderable('Framework.Renderables.CViewFilePartialRenderable');

		//Create renderer and render
		$renderer = new \Framework\Renderables\CViewFilePartialRenderable;
		$renderer->initialize($path, array('render' => $file));
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
		$_vars = array_merge($this->_view->toArray(), $_vars);
		extract($_vars);

		//Require the file
		require($file);
	}
}
?>
