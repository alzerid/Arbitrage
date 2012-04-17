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
		preg_match_all('/%(\.[0-9]*)?[a-zA-Z]/s', $this->_format, $matches);
		$numbers = ((isset($matches[1]))? $matches[1] : array());

		foreach($matches[0] as $idx=>$m)
		{
			$key = preg_replace('/^%.*([A-Za-z])$/', '$1', $m); 
			if(isset($vals[$key]) && !empty($numbers[$idx]))
			{
				$val    = $this->_formatNumber($vals[$key], $numbers[$idx]);
				$format = preg_replace('/' . $m . '/', $val, $format);
			}
			elseif(isset($vals[$key]))
			{
				$val    = $vals[$key];
				$format = preg_replace('/' . $m . '/', $vals[$key], $format);
			}
		}

		return $format;
	}

	private function _formatNumber($value, $format)
	{
		$format = "%" . $format . 'f';
		$value  = floatval($value);
		return sprintf($format, $value);
	}
}
?>
