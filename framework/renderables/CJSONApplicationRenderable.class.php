<?
namespace Framework\Renderables;
use \Framework\Renderables\CJSONRenderable;

class CJSONApplicationRenderable extends CJSONRenderable
{
	public function render($data=NULL)
	{
		ob_start();
		ob_implicit_flush(false);
		header('Content-Type: application/json');

		echo json_encode($this->renderJSON($data));

		return ob_get_clean();
	}

	public function renderJSON($json)
	{
		static $default = array('header' => array('type'    => 'application',
		                                          'scope'   => '',
		                                          'errno'   => 0,
		                                          'message' => 'success'),
		                        'user'   => array());

		//Merge
		$ret = $default;
		$ret['header'] = array_merge($ret['header'], ((isset($json['header']))? $json['header'] : array()));
		$ret['user']   = array_merge($ret['user'], ((isset($json['user']))? $json['user'] : array()));

		return $ret;
	}
}
?>
