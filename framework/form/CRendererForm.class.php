<?
namespace Framework\Forms;
use \Arbitrage2\Interfaces\IFileRenderable;

Class CRenderForm extends CForm implements IFileRenderable
{
	private $_file;
	private $_vars;

	public function __construct($properties)
	{
		if(empty($properties['render']))
			throw new EArbitrageException("Render property not defined!");

		$this->_file = $properties['render'];
		$this->_vars = ((!empty($properties['variables']))? $properties['variables'] : NULL);

		parent::__construct($properties);
	}

	public function render($data=NULL)
	{
		return $this->renderPartialFile($this->_file, $this->_vars);
	}

	public function renderPartialFile($file, $variables=NULL)
	{
		$_vars = $variables;
		if($_vars !== NULL)
			extract($_vars);

		//Generate file path
		$view_path = CApplication::getConfig()->_internals->approotpath . "app/views/";
		$path      = $view_path . $file . ".php";
		
		if(!file_exists($path))
			throw new EArbitrageException("View file does not exist '$path'.");

		ob_start();
		ob_implicit_flush(false);
		require($path);
		$content = ob_get_clean();

		return $content;

	}

	public function renderFile($file, $layout, $variables)
	{
	}
}
?>
