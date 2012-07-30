<?
namespace Framework\ClientMVC\Renderables;
$_application->requireRenderable('Framework.Renderables.CJSONApplicationRenderable');
$_application->requireRenderable('Framework.Renderables.CViewFileRenderable');

class CJSONClientMVCRenderable extends \Framework\Renderables\CViewFileRenderable
{
	public function render()
	{
		//Ensure we have array set
		!isset($this->_content['header']) && $this->_content['header'] = array();
		!isset($this->_content['client']) && $this->_content['client'] = array();
		!isset($this->_content['client']['layout']) && $this->_content['client']['layout'] = $this->_layout;
		!isset($this->_content['client']['canvas']) && $this->_content['client']['canvas'] = array();

		//TODO: Render each canvas
		foreach($this->_content['client']['canvas'] as &$canvas)
		{
			$file      = $canvas['render'];
			$variables = ((isset($canvas['variables']))? $canvas['variables'] : array());
			$canvas    = parent::renderPartial($file, $variables);
		}
		
		//Create CJSONApplicationRenderable
		$json = new \Framework\Renderables\CJSONApplicationRenderable();
		$json->initialize($this->_content);
		return $json->render();
	}
}
?>
