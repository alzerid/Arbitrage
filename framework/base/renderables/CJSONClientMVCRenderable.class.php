<?
class CJSONClientMVCRenderable extends CArrayObject implements IRenderable
{
	public function __construct()
	{
		$arr = array('header' => array('type'    => 'client',
		                               'scope'   => '',
		                               'errno'   => 0,
		                               'message' => 'success'),

		             'client' => array('canvas' => array(),
								                   'layout' => 'default'),

		             'user'   => array());
	
		//Call parent
		parent::__construct($arr);
	}

	public function setLayout($layout)
	{
		$this->client->layout = preg_replace('/\.php$/i', $layout, '');
	}

	public function render()
	{
		//Get layout
		$json = new CJSONRenderable($this->toArray());
		return $json->render();
	}
}
?>
