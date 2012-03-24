<?
class CStringFormatter
{
	private $_format;

	public function __construct($format)
	{
		$this->_format = $format;
	}

	public function format($vals)
	{
		//Find all %X
		$matches = array();
		$format  = $this->_format;
		preg_match_all('/%[a-zA-Z]/s', $this->_format, $matches);

		foreach($matches[0] as $m)
		{
			$key = substr($m, 1);
			if(isset($vals[$key]))
			{
				$val    = $vals[$key];
				$format = preg_replace('/' . $m . '/', $vals[$key], $format);
			}
		}

		return $format;
	}
}
?>
