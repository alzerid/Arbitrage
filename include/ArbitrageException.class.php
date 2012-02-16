<?
class ArbitrageException extends Exception
{
	static public $UNKNOWN = -1;

	public function getScope()
	{
		return "Arbitrage Framework";
	}

	protected function _mapMessage($err, $vars=NULL)
	{
		return "Unknown error occurred.";
	}

	protected function _replaceFormattedString($str, $vars)
	{
		foreach($vars as $key=>$val)
			$str = preg_replace('/{{' . $key . '}}/', $val, $str);

		return $str;
	}
}
?>
