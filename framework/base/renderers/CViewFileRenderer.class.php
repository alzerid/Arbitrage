<?
class CViewFileRenderer extends CRenderer implements IViewFileRenderer
{
	private $_layout;
	private $_file;
	private $_variables;

	public function render($file, $layout, $variables)
	{
		$this->_file      = $file;
		$this->_layout    = $layout;
		$this->_variables = $variables;

		return $this->_context->renderInternal($this);
	}

	public function getFile()
	{
		return $this->_file;
	}

	public function getLayout()
	{
		return $this->_layout;
	}

	public function getVariables()
	{
		return $this->_variables;
	}
}
?>
