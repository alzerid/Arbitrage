<?
class CJSONClientMVCRenderable implements IRenderable
{
	private $_data;
	public function __construct()
	{
		$arr = array('header' => array('type'    => 'client',
		                               'scope'   => '',
		                               'errno'   => 0,
		                               'message' => 'success'),

		             'client' => array('canvas' => array(),
								                   'layout' => 'default'),

		             'user'   => array());

		$this->_data = $arr;
	}

	public function setLayout($layout)
	{
		$this->client->layout = preg_replace('/\.php$/i', $layout, '');
	}

	public function render($data=NULL)
	{
		die('in CJSONClientMVCRenderable');
		//Get layout
		$json = new CJSONRenderable($this->toArray());
		return $json->render();
	}
}
?>
