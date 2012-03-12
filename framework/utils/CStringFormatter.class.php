<?
class CStringFormatter
{
	private $_values;

	public function __construct($values)
	{
		$this->_values = $values;
	}

	public function format($format)
	{
		//Find all %X
		$matches = array();
		preg_match_all('/%[a-zA-Z]/s', $format, $matches);

		foreach($matches[0] as $m)
		{
			$key = substr($m, 1);
			if(isset($this->_values[$key]))
			{
				$val    = $this->_values[$key];
				$format = preg_replace('/' . $m . '/', $this->_values[$key], $format);
			}
		}

		return $format;
	}
}
?>
