<?
namespace Framework\Renderables;
$_application->requireRenderable('Framework.Renderables.CJSONRenderable');

class CJSONApplicationRenderable extends \Framework\Renderables\CJSONRenderable
{
	public function render()
	{
		static $defaults = array('header' => array('type'    => 'application',
		                                           'scope'   => '',
		                                           'errno'   => 0,
		                                           'message' => 'success'),
		                         'user'   => array());

		//Merge header
		$return = $this->_content;
		$return['header'] = array_merge($defaults['header'], $return['header']);
		$return['user']   = array_merge($defaults['user'], isset($return['user'])? $return['user'] : array());

		//Set content
		$this->_content = array('render' => $return);

		return parent::render();
	}
}
?>
