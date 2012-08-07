<?
namespace Framework\SPA\Renderables;
$_application->requireRenderable('Framework.Renderables.CJSONApplicationRenderable');
$_application->requireRenderable('Framework.Renderables.CViewFileRenderable');

class CJSONSPARenderable extends \Framework\Renderables\CViewFileRenderable
{
	public function render()
	{
		//Set content
		$content = array();

		//Ensure we have array set
		!isset($content['header']) && $content['header'] = array('type' => 'client_mvc');
		!isset($content['client_mvc']) && $content['client_mvc'] = array();
		!isset($content['client_mvc']['layout']) && $content['client_mvc']['layout'] = $this->_layout;

		//Set content
		$content = array_merge($content, $this->_content);

		//Generate and create client_mvc portion of the return
		unset($content['render']);
		unset($content['variables']);

		//Render each canvas
		foreach($content['client_mvc']['canvas'] as $key => $canvas)
		{
			$file      = $canvas['render'];
			$variables = ((isset($canvas['variables']))? $canvas['variables'] : array());
			$canvas    = parent::renderPartial($file, $variables);

			//Set canvas html
			$content['client_mvc']['canvas'][$key] = $canvas;
		}
		
		//Create CJSONApplicationRenderable
		$json = new \Framework\Renderables\CJSONApplicationRenderable();
		$json->initialize($content);
		return $json->render();
	}
}
?>
