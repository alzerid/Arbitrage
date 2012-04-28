<?
class CSoapClient extends SoapClient
{
	public function __call($function, $args)
	{
		try
		{
			$args[0] = $this->_normalizeArguments($args[0]);
			var_dump($args[0]);
			$res = parent::__call($function, $args);
		}
		catch(Exception $ex)
		{
			$this->_debug();
			die();
		}

		$this->_debug();

		return $res;
	}

	private function _normalizeArguments($args)
	{
		foreach($args as $key=>&$val)
		{
			if($val instanceof CSoapComplexType)
			{
				$ret = $this->_normalizeArguments(get_object_vars($val));
				$val->setVariables($ret);
				$val = $val->toSoapVar();
			}
		}

		return $args;
	}

	private function _debug()
	{
		echo "REQUEST:\n\n";
		var_dump($this->__getLastRequestHeaders());
		var_dump($this->__getLastRequest());

		echo "RESPONSE:\n\n";
		var_dump($this->__getLastResponseHeaders());
		var_dump($this->__getLastResponse());
	}
}
?>
