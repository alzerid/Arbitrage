<?
class CTemplate implements ITemplate
{
	protected $_contents;

	public function __construct($contents)
	{
		$this->_contents = $contents;
	}

	public function render($variables)
	{
		preg_match_all('/{{([^}]*)}}/', $this->_contents, $matches);
		$matches = array_unique($matches[1]);
		$ret     = $this->_contents;
		foreach($matches as $val)
		{
			if(isset($variables[$val]))
			{
				$replace = preg_replace('/\$/', '\\\$', $variables[$val]);
				$ret     = preg_replace('/{{' . $val . '}}/', $replace, $ret);
			}
		}

		return $ret;
	}
}
?>
