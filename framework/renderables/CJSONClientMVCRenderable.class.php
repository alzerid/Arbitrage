<?
namespace Framework\Renderables;
use \Framework\Renderables\CJSONApplicationRenderable;

class CJSONClientMVCRenderable extends CJSONApplicationRenderable
{
	private $_layout;
	public function __construct()
	{
		$this->_layout = 'default';
	}

	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	public function renderJSON($json)
	{
		static $default = array('header' => array('type' => 'client'), 'client' => array('canvas' => array()));

		$ret = parent::renderJSON($json);
		$ret['header']['type']   = 'client';
		$ret['client']['layout'] = ((isset($json['layout']))? $json['layout'] : $this->_layout);

		//Render canvases
		$canvases = ((isset($json['client']) && isset($json['client']['canvas']))? $json['client']['canvas'] : array());
		if(count($canvases))
		{
			$dcanvas = array('file' => CApplication::getInstance()->getController()->getName() . "/" . CApplication::getInstance()->getController()->getAction()->getName(), 'variables' => '');
			$partial = new CViewFilePartialRenderable;

			//Iterate through canvases
			foreach($canvases as $canvas => $data)
			{
				!isset($data['file']) && $data['file'] = $dcanvas['file'];
				!isset($data['variables']) && $data['variables'] = $dcanvas['variables'];

				$ret['client']['canvas'][$canvas] = $partial->render($data);
			}
		}

		return $ret;
	}
}
?>
