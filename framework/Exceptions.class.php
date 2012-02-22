<?
class EArbitrageException extends Exception
{
	protected $_scope;

	public function __construct($scope = "Arbitrage Framework", $message="", $code=0, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
		$this->_scope = $scope;
	}

	public function getScope()
	{
		return $this->_scope;
	}
}

final class PHPException extends EArbitrageException
{
	public function __construct($message, $code, $file, $line, $previous=NULL)
	{
		parent::__construct("PHP Error", $message, $code, $previous);
		$this->file   = $file;
		$this->line   = $line;
		$this->_scope = "PHP Error";
	}
}

final class EArbitrageConfigException extends EArbitrageException
{
	public function __construct($message="", $code=0, $previous=NULL)
	{
		parent::__construct("Arbitrage Configuration Exception", $message, $code, $previous);
	}
}

final class EArbitrageRemoteCacheException extends EArbitrageException
{
	public function __construct($message="", $code=0, $previous=NULL)
	{
		parent::__construct("Arbitrage Remote Cache Exception", $message, $code, $previous);
	}
}
?>